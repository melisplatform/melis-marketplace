<?php

namespace MelisMarketPlace\Controller;

use Laminas\Http\PhpEnvironment\Response as HttpResponse;
use MelisCore\Controller\MelisAbstractActionController;

/**
 * JSON API for the native React "Market Place" list (browse packages from the
 * Melis Packagist server). Lives INSIDE MelisMarketPlace (not melis-react-api) since
 * it is specific to this module and reuses MelisMarketPlaceService directly.
 *
 * Read-only viewer: install/update/remove/setup-form actions stay on the legacy tool
 * (rendered in an iframe on the React record route) — reimplementing composer/dbdeploy/
 * filesystem mutation logic natively is out of scope for a list migration.
 *
 * Routes (registered in MelisMarketPlace's own config/module.config.php):
 *   GET /melis/MelisMarketPlace/react-api/packages  → paginated/searchable package list
 *   GET /melis/MelisMarketPlace/react-api/groups    → package groups (filter options)
 *   GET /melis/MelisMarketPlace/react-api/stats     → KPI cards
 *   GET /melis/MelisMarketPlace/react-api/status    → marketplace server reachability
 */
class MelisMarketPlaceReactApiController extends MelisAbstractActionController
{
    /** melisKey of the tool — used by the rights guard (see denyUnlessAccess). */
    private const MELIS_KEY = 'melis_market_place_tool_display';

    // ─── GET /packages ──────────────────────────────────────────────────────────

    public function packagesAction(): HttpResponse
    {
        if ($deny = $this->denyUnlessAccess()) { return $deny; }

        try {
            if (!$this->isMarketplaceAccessible()) {
                return $this->jsonResponse([
                    'success' => true,
                    'data' => ['items' => [], 'page' => 1, 'pageCount' => 0, 'limit' => 0, 'marketAccessible' => false],
                ]);
            }

            $page     = max(1, (int) $this->params()->fromQuery('page', 1));
            $limit    = min(200, max(1, (int) $this->params()->fromQuery('limit', 24)));
            $search   = trim((string) ($this->params()->fromQuery('search', '') ?? ''));
            $group    = trim((string) ($this->params()->fromQuery('group', '') ?? ''));
            $orderBy  = trim((string) ($this->params()->fromQuery('orderBy', 'mp_total_downloads') ?? ''));
            $order    = trim((string) ($this->params()->fromQuery('order', 'desc') ?? ''));
            $bundle   = (int) (bool) $this->params()->fromQuery('bundle', 0);

            $serverPackages = $this->fetchFromPackagist('/get-packages/page/' . $page
                . '/search/' . urlencode($search)
                . '/item_per_page/' . $limit
                . '/order/' . $order
                . '/order_by/' . $orderBy
                . '/status/1/group/' . $group
                . '/bundle/' . $bundle);

            $rawItems = $serverPackages['packages'] ?? [];
            // Récupère les vraies dernières versions EN PARALLÈLE (une seule salve réseau pour la page).
            $this->primeLatestVersions(array_map(static fn ($x) => (string) ($x['packageName'] ?? ''), $rawItems));
            $items = array_map([$this, 'formatPackage'], $rawItems);

            // Melis Packagist paginates by page/pageCount (no absolute item total is returned).
            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'items'            => $items,
                    'page'             => (int) ($serverPackages['currentPageNumber'] ?? $page),
                    'pageCount'        => (int) ($serverPackages['pageCount'] ?? 1),
                    'limit'            => (int) ($serverPackages['itemCountPerPage'] ?? $limit),
                    'marketAccessible' => true,
                ],
            ]);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    // ─── GET /groups ────────────────────────────────────────────────────────────

    public function groupsAction(): HttpResponse
    {
        if ($deny = $this->denyUnlessAccess()) { return $deny; }

        try {
            if (!$this->isMarketplaceAccessible()) {
                return $this->jsonResponse(['success' => true, 'data' => ['groups' => [], 'marketAccessible' => false]]);
            }

            // Shape: [{ mp_group_id, mp_group_name (e.g. "MelisCore"), module_count }, ...]
            $groupData = $this->fetchFromPackagist('/get-package-group');
            $groups = [];
            foreach ((array) $groupData as $g) {
                if (!is_array($g)) { continue; }
                $rawName = (string) ($g['mp_group_name'] ?? '');
                // Legacy display strips the "Melis" prefix (5 chars): "MelisCore" → "Core".
                $displayName = str_starts_with($rawName, 'Melis') ? substr($rawName, 5) : $rawName;
                $groups[] = [
                    'id'   => isset($g['mp_group_id']) ? (int) $g['mp_group_id'] : null,
                    'name' => $displayName !== '' ? $displayName : $rawName,
                ];
            }

            return $this->jsonResponse(['success' => true, 'data' => ['groups' => $groups, 'marketAccessible' => true]]);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    // ─── GET /stats ─────────────────────────────────────────────────────────────

    public function statsAction(): HttpResponse
    {
        if ($deny = $this->denyUnlessAccess()) { return $deny; }

        try {
            if (!$this->isMarketplaceAccessible()) {
                return $this->jsonResponse([
                    'success' => true,
                    'data' => ['total' => 0, 'installed' => 0, 'needUpdate' => 0, 'marketAccessible' => false],
                ]);
            }

            $serverPackages = $this->fetchFromPackagist(
                '/get-packages/page/1/search//item_per_page/0/order/desc/order_by/mp_total_downloads/status/1/group//bundle/0'
            );
            $rawItems = $serverPackages['packages'] ?? [];
            // KPI seulement : on N'INTERROGE PAS packagist.org (43 appels pour un compteur). On utilise
            // ce que la liste a déjà mis en cache (fetch=false), repli packageVersion pour le reste.
            $this->primeLatestVersions(array_map(static fn ($x) => (string) ($x['packageName'] ?? ''), $rawItems), false);
            $items = array_map([$this, 'formatPackage'], $rawItems);

            $installed  = 0;
            $needUpdate = 0;
            foreach ($items as $item) {
                if ($item['installed']) { $installed++; }
                if ($item['versionStatus'] === 'need_update') { $needUpdate++; }
            }

            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'total'            => count($items),
                    'installed'        => $installed,
                    'needUpdate'       => $needUpdate,
                    'marketAccessible' => true,
                ],
            ]);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    // ─── GET /packages/:id ────────────────────────────────────────────────────

    public function getAction(): HttpResponse
    {
        if ($deny = $this->denyUnlessAccess()) { return $deny; }

        $id = (int) $this->params()->fromRoute('id', 0);
        if ($id <= 0) {
            return $this->jsonResponse(['success' => false, 'error' => 'Invalid ID'], 400);
        }

        try {
            if (!$this->isMarketplaceAccessible()) {
                return $this->jsonResponse(['success' => false, 'error' => 'Marketplace unreachable'], 503);
            }

            $raw = $this->fetchFromPackagist('/get-package/' . $id);
            if (!$raw || !isset($raw['packageId'])) {
                return $this->jsonResponse(['success' => false, 'error' => 'Not found'], 404);
            }

            $this->primeLatestVersions([(string) ($raw['packageName'] ?? '')]);
            $item = $this->formatPackage($raw);

            $currentVersion = null;
            if ($item['moduleName'] !== '' && $item['installed']) {
                $info = $this->getServiceManager()->get('MelisAssetManagerModulesService')->getModulesAndVersions($item['moduleName']);
                $currentVersion = $info['version'] ?? null;
            }
            $item['currentVersion'] = $currentVersion;
            $item['isExempted'] = $item['moduleName'] !== '' && in_array($item['moduleName'], $this->getModuleExceptions(), true);

            return $this->jsonResponse(['success' => true, 'data' => $item]);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    // ─── GET /status ────────────────────────────────────────────────────────────

    public function statusAction(): HttpResponse
    {
        if ($deny = $this->denyUnlessAccess()) { return $deny; }

        return $this->jsonResponse(['success' => true, 'data' => ['accessible' => $this->isMarketplaceAccessible()]]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────────

    /** DB row (Melis Packagist package shape) → camelCase API item. */
    private function formatPackage(array $p): array
    {
        $moduleName = (string) ($p['packageModuleName'] ?? '');
        $installed  = $moduleName !== '' ? $this->isModuleInstalled($moduleName) : false;

        // Vraie dernière version publiée (packagist.org) — le packageVersion du catalogue Melis peut
        // être périmé. Repli sur packageVersion si packagist.org est injoignable / package absent.
        $packageName = (string) ($p['packageName'] ?? '');
        $latest      = (string) ($this->latestVersion($packageName, (string) ($p['packageVersion'] ?? '')) ?? '');

        $versionStatus = null;
        if ($moduleName !== '') {
            // Le statut (à jour / à mettre à jour) se compare à la VRAIE dernière version.
            $status = $this->getMarketPlaceService()->compareLocalVersionFromRepo($moduleName, $latest);
            $versionStatus = match ($status) {
                \MelisMarketPlace\Service\MelisMarketPlaceService::NEED_UPDATE => 'need_update',
                \MelisMarketPlace\Service\MelisMarketPlaceService::UP_TO_DATE => 'up_to_date',
                \MelisMarketPlace\Service\MelisMarketPlaceService::IN_ADVANCE => 'in_advance',
                default => null,
            };
        }

        $rawGroupName = (string) ($p['packageGroupName'] ?? '');

        // On expose l'URL React (branche melis-react + react/) ET l'URL legacy d'origine : le front
        // tente la React puis retombe (onError) sur la legacy via data-legacy — le repli exact décrit
        // côté produit (« si les images react ne s'affichent pas, on affiche les anciennes »).
        $rawImages   = $p['packageImages'] ?? [];
        $mainRaw     = $this->mainImageRaw($rawImages);
        $allRaw      = $this->allImagesRaw($rawImages);

        return [
            'id'             => (int) ($p['packageId'] ?? 0),
            'title'          => (string) ($p['packageTitle'] ?? ''),
            'name'           => (string) ($p['packageName'] ?? ''),
            'subtitle'       => (string) ($p['packageSubtitle'] ?? ''),
            'moduleName'     => $moduleName,
            'description'    => (string) ($p['packageDescription'] ?? ''),
            'image'          => $this->reactMainImageUrl($mainRaw),
            'imageLegacy'    => $mainRaw,
            'images'         => array_map(fn ($u) => $this->reactImageUrl($u), $allRaw),
            'imagesLegacy'   => $allRaw,
            'url'            => $p['packageUrl'] ?? null,
            'repository'     => $p['packageRepository'] ?? null,
            'totalDownloads' => (int) ($p['packageTotalDownloads'] ?? 0),
            'version'        => $latest !== '' ? $latest : (string) ($p['packageVersion'] ?? ''),
            'releaseDate'    => $p['packageTimeOfRelease'] ?? null,
            'maintainers'    => $p['packageMaintainers'] ?? null,
            'type'           => $p['packageType'] ?? null,
            'dateAdded'      => $p['packageDateAdded'] ?? null,
            'lastUpdate'     => $p['packageLastUpdate'] ?? null,
            'groupId'        => isset($p['packageGroupId']) ? (int) $p['packageGroupId'] : null,
            // Legacy display strips the "Melis" prefix (5 chars): "MelisMarketing" → "Marketing".
            'groupName'      => str_starts_with($rawGroupName, 'Melis') ? substr($rawGroupName, 5) : $rawGroupName,
            'isActive'       => (bool) ($p['packageIsActive'] ?? false),
            'isPrivate'      => (bool) ($p['packageIsPrivate'] ?? false),
            'installed'      => $installed,
            'versionStatus'  => $versionStatus,
        ];
    }

    private function isModuleInstalled(string $module): bool
    {
        return (bool) $this->getServiceManager()->get('MelisAssetManagerModulesService')->getModulePath($module);
    }

    /** L'URL d'origine (legacy) de l'image principale, telle que fournie par le catalogue. */
    private function mainImageRaw(array $images): ?string
    {
        foreach ($images as $img) {
            if (is_array($img) && (string) ($img['imageIsMain'] ?? '') === '1') {
                return $img['imageFile'] ?? null;
            }
        }
        return $images[0]['imageFile'] ?? null;
    }

    /** Toutes les URLs d'origine (legacy). @param array<mixed> $images @return string[] */
    private function allImagesRaw(array $images): array
    {
        return array_values(array_filter(array_map(fn ($img) => is_array($img) ? ($img['imageFile'] ?? null) : null, $images)));
    }

    /**
     * Version React de la marketplace : on affiche les NOUVELLES captures d'écran, rangées dans le
     * sous-dossier « react/ » du dossier images de chaque module (etc/MarketPlace/images/react/…),
     * tandis que la version legacy continue d'utiliser l'URL d'origine.
     *
     * Deux transformations sur l'URL GitHub raw du catalogue :
     *  1) insérer « react/ » juste avant le nom de fichier
     *     (…/etc/MarketPlace/images/melis-slider_1.JPG → …/etc/MarketPlace/images/react/melis-slider_1.JPG) ;
     *  2) réécrire le segment de VERSION vers la branche « melis-react ».
     *
     * (2) est indispensable : le serveur packagist Melis référence les images sur d'ANCIENS tags
     * (ex. v3.1.x) qui ne contiennent pas le dossier react/ → l'URL react y renverrait 404 en
     * permanence (donc toujours le repli legacy). Les images react vivent sur la branche `melis-react`
     * (et sur les tags récents), donc on pointe la ref sur cette branche — indépendamment du tag
     * (potentiellement périmé) renvoyé par le catalogue. Le front garde l'URL legacy D'ORIGINE
     * (imageLegacy) pour le repli onError, donc un module sans images react (ou sans branche
     * melis-react) retombe proprement sur l'ancienne image. On ne touche pas aux URLs non-GitHub
     * (ex. médias hébergés par le serveur marketplace) : elles restent identiques.
     */
    private function reactImageUrl(?string $url): ?string
    {
        if (empty($url)) {
            return $url;
        }
        // (1) …/images/<fichier> → …/images/react/<fichier>
        $out = preg_replace('#(/etc/MarketPlace/images)/(?=[^/]+$)#', '$1/react/', $url) ?? $url;
        // (2) raw.githubusercontent.com/melisplatform/<repo>/<ref>/… → …/<repo>/melis-react/…
        $out = preg_replace('#(raw\.githubusercontent\.com/melisplatform/[^/]+)/[^/]+/#', '$1/melis-react/', $out) ?? $out;
        return $out;
    }

    /**
     * Image principale de la vignette de LISTE : comme reactImageUrl, mais on force l'index à 1.
     *
     * Le catalogue packagist ne renvoie qu'UNE image par module et la désigne « principale » de
     * façon peu fiable (souvent …_5, …_2 — l'effet « aléatoire » remonté). Or, par convention, la
     * PREMIÈRE image du lot (…_1.<ext>, ou …-1.<ext>) est la cover spécialement conçue pour être la
     * plus attractive. On réécrit donc le suffixe numérique du fichier en « 1 ». Les fichiers sans
     * suffixe numérique (ex. melis-marketplace.JPG) sont laissés tels quels. Le repli onError reste
     * l'URL legacy d'origine (imageLegacy), donc un module dont le …_1 n'existe pas retombe proprement.
     */
    private function reactMainImageUrl(?string $url): ?string
    {
        $u = $this->reactImageUrl($url);
        if (empty($u)) {
            return $u;
        }
        return preg_replace('#([_-])\d+(\.[A-Za-z0-9]+)$#', '${1}1$2', $u) ?? $u;
    }

    /** Modules that cannot be removed/updated via the marketplace (config: …/datas/exceptions). */
    private function getModuleExceptions(): array
    {
        $config = $this->getServiceManager()->get('MelisConfig');
        $datas  = $config->getItem('melismarketplace_toolstree_section/datas/');
        return $datas['exceptions'] ?? [];
    }

    private function getMarketPlaceService(): \MelisMarketPlace\Service\MelisMarketPlaceService
    {
        return $this->getServiceManager()->get('MelisMarketPlaceService');
    }

    /** Melis Packagist server base URL (config: melismarketplace_toolstree_section/datas/melis_packagist_server). */
    private function getMelisPackagistServer(): ?string
    {
        $config = $this->getServiceManager()->get('MelisConfig');
        $datas  = $config->getItem('melismarketplace_toolstree_section/datas/');
        return $datas['melis_packagist_server'] ?? null;
    }

    /** @return array<mixed> */
    private function fetchFromPackagist(string $path): array
    {
        $url = $this->getMelisPackagistServer();
        if (!$url) { return []; }

        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $response = @file_get_contents($url . $path);
        try {
            $decoded = \Laminas\Json\Json::decode($response, \Laminas\Json\Json::TYPE_ARRAY);
        } catch (\Throwable) {
            $decoded = [];
        }
        return is_array($decoded) ? $decoded : [];
    }

    /** @var array<string,?string> Cache par requête : nom de package → dernière version stable (packagist.org). */
    private array $latestVersionCache = [];

    /** Durée de validité du cache disque des dernières versions (packagist.org). */
    private const LATEST_VERSIONS_TTL = 3600;

    /**
     * Pré-charge la dernière version publiée pour une liste de packages, avec un cache DISQUE (TTL)
     * et une salve réseau PARALLÈLE (curl_multi) pour les entrées froides.
     *
     * Le catalogue Melis (marketplace.melisplatform.com) expose un `packageVersion` qui peut être
     * PÉRIMÉ : le module a `packageIsAutoUpdate=1` mais l'auto-update côté serveur ne suit pas les
     * nouvelles releases (ex. il renvoyait v5.3.17 alors que la dernière publiée est v6.0.x). On lit
     * donc la source de vérité publique (packagist.org) pour afficher la VRAIE dernière version, qui
     * se met à jour d'elle-même à chaque release. Sans cache, ~24 requêtes par page rendaient la liste
     * lente (8-12 s) ; le cache disque (TTL 1 h) rend les chargements suivants instantanés et « auto
     * met à jour » les versions dans l'heure d'une release. Tout échec (module absent de packagist.org,
     * réseau) laisse la valeur à null → l'appelant retombe sur `packageVersion`.
     *
     * @param string[] $names
     * @param bool $fetch false → n'utilise QUE le cache (disque), aucune requête réseau (pour les KPI :
     *                    on ne déclenche pas ~43 appels juste pour un compteur ; la liste, elle, fetch).
     */
    private function primeLatestVersions(array $names, bool $fetch = true): void
    {
        $wanted = array_values(array_unique(array_filter($names)));
        if (!$wanted) { return; }

        $disk = $this->readVersionCacheFile();
        $now  = time();
        $todo = [];
        foreach ($wanted as $n) {
            if (array_key_exists($n, $this->latestVersionCache)) { continue; }
            if (isset($disk[$n]) && is_array($disk[$n]) && ($now - (int) ($disk[$n]['t'] ?? 0)) < self::LATEST_VERSIONS_TTL) {
                $this->latestVersionCache[$n] = $disk[$n]['v'] ?? null; // cache disque frais
            } else {
                $todo[] = $n;
            }
        }
        if (!$todo || !$fetch) { return; }

        $mh = curl_multi_init();
        $handles = [];
        foreach ($todo as $n) {
            $this->latestVersionCache[$n] = null; // défaut = repli sur packageVersion
            $ch = curl_init('https://repo.packagist.org/p2/' . $n . '.json');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 5,
                CURLOPT_CONNECTTIMEOUT => 3,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_ENCODING       => '',  // accepte gzip → réponse ~6× plus petite
                CURLOPT_USERAGENT      => 'MelisMarketPlace',
            ]);
            curl_multi_add_handle($mh, $ch);
            $handles[$n] = $ch;
        }
        do {
            $status = curl_multi_exec($mh, $running);
            if ($running) { curl_multi_select($mh, 1.0); }
        } while ($running && $status === CURLM_OK);

        foreach ($handles as $n => $ch) {
            $body = (string) curl_multi_getcontent($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($code === 200 && $body !== '') {
                $this->latestVersionCache[$n] = $this->pickLatestStable($n, $body);
            }
            // On mémorise MÊME les échecs (valeur null) pour ne pas retenter à chaque requête pendant le TTL.
            $disk[$n] = ['v' => $this->latestVersionCache[$n], 't' => $now];
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }
        curl_multi_close($mh);
        $this->writeVersionCacheFile($disk);
    }

    private function versionCacheFilePath(): string
    {
        return sys_get_temp_dir() . '/melis-marketplace-latest-versions.json';
    }

    /** @return array<string,array{v:?string,t:int}> */
    private function readVersionCacheFile(): array
    {
        $f = $this->versionCacheFilePath();
        if (!is_readable($f)) { return []; }
        $data = json_decode((string) @file_get_contents($f), true);
        return is_array($data) ? $data : [];
    }

    /** @param array<string,mixed> $data */
    private function writeVersionCacheFile(array $data): void
    {
        // Re-lire et fusionner avant d'écrire : la liste (page courante) et les stats (tous les
        // packages) tournent en requêtes CONCURRENTES ; sans fusion, la dernière écriture écraserait
        // les entrées de l'autre et le cache ne se réchaufferait jamais complètement.
        $merged = array_merge($this->readVersionCacheFile(), $data);
        @file_put_contents($this->versionCacheFilePath(), json_encode($merged), LOCK_EX);
    }

    /** Plus récente version STABLE d'une réponse packagist.org p2 (versions listées du + récent au + ancien). */
    private function pickLatestStable(string $name, string $json): ?string
    {
        $data     = json_decode($json, true);
        $versions = $data['packages'][$name] ?? null;
        if (!is_array($versions)) { return null; }
        $fallback = null;
        foreach ($versions as $v) {
            $ver = (string) ($v['version'] ?? '');
            if ($ver === '') { continue; }
            if ($fallback === null) { $fallback = $ver; }   // 1re entrée = la plus récente (pré-releases incluses)
            $low = strtolower($ver);
            if (str_starts_with($low, 'dev-') || str_contains($low, '-dev')) { continue; }
            if (preg_match('/(alpha|beta|rc)/i', $ver)) { continue; }
            return $ver;                                    // 1re version stable rencontrée
        }
        return $fallback;
    }

    /**
     * Dernière version d'un package, LUE dans le cache de requête (rempli par primeLatestVersions),
     * avec repli sur $fallback (packageVersion). Ne déclenche AUCUNE requête réseau ici : c'est
     * l'appelant (liste/détail) qui décide de fetcher via primeLatestVersions, pour maîtriser la
     * latence (une salve parallèle par page, pas un appel synchrone caché par package).
     */
    private function latestVersion(string $name, ?string $fallback): ?string
    {
        if ($name === '') { return $fallback; }
        return $this->latestVersionCache[$name] ?? $fallback;
    }

    private function isMarketplaceAccessible(): bool
    {
        $url = $this->getMelisPackagistServer();
        if (!$url) { return false; }

        $ch = curl_init($url . '/get-package-group');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_NOBODY => true,
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_NOSIGNAL => 1,
        ]);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode > 0 && $httpCode < 400;
    }

    private function isAuthenticated(): bool
    {
        return $this->getServiceManager()->get('MelisCoreAuth')->hasIdentity();
    }

    /**
     * Rights guard: every endpoint requires ACCESS to the tool (melis_market_place_tool_display),
     * not just a session — closes the API/URL back-door. 401/403/null.
     */
    private function denyUnlessAccess(): ?HttpResponse
    {
        if (!$this->isAuthenticated()) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthenticated'], 401);
        }
        try {
            if (!$this->getServiceManager()->get('MelisCoreRights')->canAccess(self::MELIS_KEY)) {
                return $this->jsonResponse(['success' => false, 'error' => 'Forbidden'], 403);
            }
        } catch (\Throwable) {}
        return null;
    }

    private function jsonResponse(array $data, int $status = 200): HttpResponse
    {
        /** @var HttpResponse $response */
        $response = $this->getResponse();
        $response->setStatusCode($status);
        $response->getHeaders()->addHeaders([
            'Content-Type'           => 'application/json; charset=utf-8',
            'X-Content-Type-Options' => 'nosniff',
        ]);
        $response->setContent(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response;
    }

    private function errorResponse(\Throwable $e, int $status = 500): HttpResponse
    {
        return $this->jsonResponse([
            'success' => false,
            'error'   => $e->getMessage(),
            'file'    => basename($e->getFile()) . ':' . $e->getLine(),
        ], $status);
    }
}
