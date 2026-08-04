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

        $versionStatus = null;
        if ($moduleName !== '') {
            $status = $this->getMarketPlaceService()->compareLocalVersionFromRepo($moduleName, $p['packageVersion'] ?? null);
            $versionStatus = match ($status) {
                \MelisMarketPlace\Service\MelisMarketPlaceService::NEED_UPDATE => 'need_update',
                \MelisMarketPlace\Service\MelisMarketPlaceService::UP_TO_DATE => 'up_to_date',
                \MelisMarketPlace\Service\MelisMarketPlaceService::IN_ADVANCE => 'in_advance',
                default => null,
            };
        }

        $rawGroupName = (string) ($p['packageGroupName'] ?? '');

        return [
            'id'             => (int) ($p['packageId'] ?? 0),
            'title'          => (string) ($p['packageTitle'] ?? ''),
            'name'           => (string) ($p['packageName'] ?? ''),
            'subtitle'       => (string) ($p['packageSubtitle'] ?? ''),
            'moduleName'     => $moduleName,
            'description'    => (string) ($p['packageDescription'] ?? ''),
            'image'          => $this->mainImage($p['packageImages'] ?? []),
            'images'         => $this->allImages($p['packageImages'] ?? []),
            'url'            => $p['packageUrl'] ?? null,
            'repository'     => $p['packageRepository'] ?? null,
            'totalDownloads' => (int) ($p['packageTotalDownloads'] ?? 0),
            'version'        => (string) ($p['packageVersion'] ?? ''),
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

    /** @param array<mixed> $images */
    private function mainImage(array $images): ?string
    {
        foreach ($images as $img) {
            if (is_array($img) && (string) ($img['imageIsMain'] ?? '') === '1') {
                return $this->reactImageUrl($img['imageFile'] ?? null);
            }
        }
        return $this->reactImageUrl($images[0]['imageFile'] ?? null);
    }

    /** @param array<mixed> $images @return string[] */
    private function allImages(array $images): array
    {
        return array_values(array_filter(array_map(fn ($img) => is_array($img) ? $this->reactImageUrl($img['imageFile'] ?? null) : null, $images)));
    }

    /**
     * Version React de la marketplace : on affiche les NOUVELLES captures d'écran, rangées dans le
     * sous-dossier « react/ » du dossier images de chaque module (etc/MarketPlace/images/react/…),
     * tandis que la version legacy continue d'utiliser l'URL d'origine. On insère donc « react/ »
     * juste avant le nom de fichier de l'URL du catalogue (ex. GitHub raw
     * …/etc/MarketPlace/images/melis-slider_1.JPG → …/etc/MarketPlace/images/react/melis-slider_1.JPG).
     * NB : ces nouvelles images n'existent que sur les versions des modules pas encore publiées ;
     * l'URL pointera au bon endroit dès leur publication. On ne touche que le suffixe du lien.
     */
    private function reactImageUrl(?string $url): ?string
    {
        if (empty($url)) {
            return $url;
        }
        return preg_replace('#(/etc/MarketPlace/images)/(?=[^/]+$)#', '$1/react/', $url) ?? $url;
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
