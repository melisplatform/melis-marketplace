<?php
/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2017 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisMarketPlace\Service;

use Laminas\ServiceManager\ServiceManager;
use MelisCore\Service\MelisGeneralService;
use Laminas\View\Model\JsonModel;

class MelisMarketPlaceService extends MelisGeneralService
{
    /**
     * @var int NEED_UPDATE
     */
    const NEED_UPDATE = -1;

    /**
     * @var int UP_TO_DATE
     */
    const UP_TO_DATE = 1;

    /**
     * @var int IN_ADVANCE
     */
    const IN_ADVANCE = 2;

    /**
     * @var string DEV
     */
    CONST DEV = 'dev-';

    /**
     * @var string MODULE_SETUP_POST_DOWNLOAD_CONTROLLER
     */
    const MODULE_SETUP_POST_DOWNLOAD_CONTROLLER = 'MelisSetupPostDownloadController';

    /**
     * @var string MODULE_SETUP_POST_UPDATE_CONTROLLER
     */
    const MODULE_SETUP_POST_UPDATE_CONTROLLER = 'MelisSetupPostUpdateController';

    /**
     * @var string MODULE_SETUP_FORM
     */
    const MODULE_SETUP_FORM = 'getFormAction';

    /**
     * @var string MODULE_SETUP_VALIDATE_FORM
     */
    const MODULE_SETUP_VALIDATE_FORM = 'validateFormAction';

    /**
     * @var string MODULE_SETUP_SUBMIT_FORM
     */
    const MODULE_SETUP_SUBMIT_FORM = 'submitAction';

    /**
     * @var string MODULE_SETUP_FORM_SHOW_ON_MARKETPLACE
     */
    const MODULE_SETUP_FORM_SHOW_ON_MARKETPLACE = 'showOnMarketplacePostSetup';

    /**
     * @var string ACTION_DOWNLOAD
     */
    const ACTION_DOWNLOAD = 'download';

    /**
     * @var string ACTION_UPDATE
     */
    const ACTION_UPDATE = 'update';

    /**
     * @var string $action
     */
    protected $action = 'download';

    /**
     * @return string
     */
    protected function getAction()
    {
        return $this->action;
    }

    /**
     * @param $action
     */
    protected function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @param $module
     *
     * @return string|null
     */
    public function getForm($module)
    {
        if (!$this->moduleManager()->isModuleLoaded($module)) {
            $this->moduleManager()->loadModule($module);
        }

        $class = implode('\\', [$module, 'Controller', str_replace('Controller', '', $this->getActionController())]);
        $form = $this->forward()->dispatch($class, ['action' => str_replace('Action', '', self::MODULE_SETUP_FORM)]);

        /** @var \Laminas\View\Renderer\RendererInterface $renderer */
        $renderer = $this->getServiceManager()->get('Laminas\View\Renderer\RendererInterface');
        $formDom = (new \Laminas\Mime\Part($renderer->render($form)))->getContent() ?: null;

        return trim($formDom);
    }

    /**
     * @param $module
     * @param $post
     *
     * @return array|\ArrayAccess|null|\Traversable
     */
    public function validateForm($module, $post)
    {
        $class = implode('\\', [$module, 'Controller', str_replace('Controller', '', $this->getActionController())]);
        $params = array_merge(
            ['action' => str_replace('Action', '', self::MODULE_SETUP_VALIDATE_FORM)],
            ['post' => $post]);

        /** @var \Laminas\View\Model\JsonModel $result */
        $result = $this->forward()->dispatch($class, $params);

        if ($result instanceof JsonModel) {
            return $result->getVariables();
        }

        return null;
    }

    /**
     * @param $module
     * @param $post
     *
     * @return array|\ArrayAccess|null|\Traversable
     */
    public function submitForm($module, $post)
    {
        $class = implode('\\', [$module, 'Controller', str_replace('Controller', '', $this->getActionController())]);
        $params = array_merge(
            ['action' => str_replace('Action', '', self::MODULE_SETUP_SUBMIT_FORM)],
            ['post' => $post]);

        /** @var \Laminas\View\Model\JsonModel $result */
        $result = $this->forward()->dispatch($class, $params);

        if ($result instanceof JsonModel) {
            return $result->getVariables();
        }


        return null;
    }

    /**
     * @return string
     */
    public function getActionController()
    {
        switch ($this->getAction()) {
            case self::ACTION_DOWNLOAD:
                return self::MODULE_SETUP_POST_DOWNLOAD_CONTROLLER;
                break;
            case self::ACTION_UPDATE:
                return self::MODULE_SETUP_POST_UPDATE_CONTROLLER;
                break;
            default:
                return self::ACTION_DOWNLOAD;
                break;
        }

        return self::ACTION_DOWNLOAD;
    }

    /**
     * @param $module
     * @param $action
     *
     * @return bool
     * @throws \ReflectionException
     */
    public function hasPostSetup($module, $action)
    {
        $this->setAction($action);

        $namespace = implode('\\', [$module, 'Controller', $this->getActionController()]);

        if (!class_exists($namespace) && !method_exists($namespace, $this->getActionController())) {
            return false;
        }

        if ($action === self::ACTION_DOWNLOAD && $this->showSetupFormOnDownload($module)) {
            return true;
        }

        if ($action === self::ACTION_UPDATE && $this->showSetupFormOnUpdate($module)) {
            return true;
        }

        return false;
    }

    /**
     * Flag for Marketplace whether to display the setup form or not when downloading
     *
     * @param $module
     *
     * @return bool|mixed|null
     * @throws \ReflectionException
     */
    protected function showSetupFormOnDownload($module)
    {
        $moduleClass = implode('\\', [$module, 'Controller', self::MODULE_SETUP_POST_DOWNLOAD_CONTROLLER]);

        if (class_exists($moduleClass)) {
            return $this->getClassProperty($moduleClass, self::MODULE_SETUP_FORM_SHOW_ON_MARKETPLACE);
        }

        return false;
    }

    /**
     * Get the value of the class' property
     *
     * @param $class
     * @param $prop
     *
     * @return mixed|null
     * @throws \ReflectionException
     */
    protected function getClassProperty($class, $prop)
    {
        if (class_exists($class)) {
            $reflection = new \ReflectionClass($class);
            $property = $reflection->getProperty($prop)->getValue(new $class);

            return $property;
        }

        return null;
    }

    /**
     * Flag for Marketplace whether to display the setup form or not when updating
     *
     * @param $module
     *
     * @return bool|mixed|null
     * @throws \ReflectionException
     */
    protected function showSetupFormOnUpdate($module)
    {
        $moduleClass = implode('\\', [$module, 'Controller', self::MODULE_SETUP_POST_UPDATE_CONTROLLER]);

        if (class_exists($moduleClass)) {
            return $this->getClassProperty($moduleClass, self::MODULE_SETUP_FORM_SHOW_ON_MARKETPLACE);
        }

        return false;
    }

    /**
     * @param $module
     *
     * @return bool
     */
    public function plugModule($module)
    {
        return $this->moduleManager()->loadModule($module);
    }

    /**
     * @return \MelisAssetManager\Service\MelisCoreModulesService
     */
    protected function moduleManager()
    {
        /** @var \MelisAssetManager\Service\MelisCoreModulesService $service */
        $service = $this->getServiceManager()->get('MelisAssetManagerModulesService');

        return $service;
    }

    /**
     * @param $module
     *
     * @return bool
     */
    public function unplugModule($module)
    {
        return $this->moduleManager()->unloadModule($module);
    }

    /**
     * Function to get the local version of a module
     * and compare it from the repository to determine
     * whether the module is up to date or not
     *
     * @param $moduleName
     * @param $moduleVersion
     *
     * @return array
     */
    public function compareLocalVersionFromRepo($moduleName = null, $moduleVersion = null)
    {
        // Event parameters prepare
        $arrayParameters = $this->makeArrayFromParameters(__METHOD__, func_get_args());

        // Sending service start event
        $arrayParameters = $this->sendEvent('melismarketplace_compare_local_version_from_repo_start', $arrayParameters);

        $status = null;
        $moduleSvc = $this->moduleManager();
        $tmpModName = ($arrayParameters['moduleName'] == "MelisMarketplace") ? "MelisMarketPlace" : $arrayParameters['moduleName'];
        $modulesInfo = $moduleSvc->getModulesAndVersions($tmpModName);

        if (!empty($modulesInfo['version'])) {

            $localVersion = $modulesInfo['version'];
    
            //check if local version is advance or not
            if (substr(strtolower($localVersion), 0, 4) === self::DEV) {
                $status = self::IN_ADVANCE;
            } else {
                //remove the v from the version and convert to float
                //to compare the version number
                $localV = $localVersion ? str_replace('v', "", strtolower($localVersion)) : 0;
                $latestV = $arrayParameters['moduleVersion'] ? str_replace('v', "", strtolower($arrayParameters['moduleVersion'])) : 0;
    
                //check if  local version is updated than the version in repo
                if ($latestV <= $localV) {
                    $status = self::UP_TO_DATE;
                } else {
                    $status = self::NEED_UPDATE;
                }
            }
        }

        // Adding results to parameters for events treatment if needed
        $arrayParameters['results'] = $status;
        // Sending service end event
        $arrayParameters = $this->sendEvent('melismarketplace_compare_local_version_from_repo_end', $arrayParameters);

        return $arrayParameters['results'];
    }

    // ─── Dernière version publiée (packagist.org) ───────────────────────────────────────────────
    //
    // Le catalogue Melis (marketplace.melisplatform.com) expose un `packageVersion` PÉRIMÉ pour
    // beaucoup de modules : ils sont en `packageIsAutoUpdate=1` mais l'auto-update côté serveur ne
    // suit plus les releases (ex. MelisCore y est resté à v5.3.17 alors que la dernière publiée est
    // v6.0.8 — et comme 5.3.17 < la version installée, l'outil affichait « UP TO DATE » en masquant
    // une majeure). On lit donc la source de vérité publique (packagist.org), partagée ici par
    // l'outil legacy ET l'API React pour qu'ils affichent la MÊME version.

    /** @var array<string,?string> Cache par requête : nom de package → dernière version stable. */
    private array $latestVersionCache = [];

    /** @var array<string,bool> Cache par requête : nom de package → true si CONFIRMÉ absent (404). */
    private array $absentCache = [];

    /** Durée de validité du cache disque des dernières versions. */
    const LATEST_VERSIONS_TTL = 3600;

    /**
     * Remplace le `packageVersion` (périmé) de chaque package du catalogue par la VRAIE dernière
     * version publiée. La valeur d'origine est conservée dans `packageVersionCatalog`. Une seule
     * salve réseau parallèle pour tout le lot, puis cache disque 1 h.
     *
     * @param array<int,array<string,mixed>> $packages packages tels que renvoyés par le catalogue
     * @param bool $fetch false → cache disque seulement, aucune requête réseau
     * @return array<int,array<string,mixed>>
     */
    public function applyLatestVersions(array $packages, $fetch = true)
    {
        $this->primeLatestVersions(array_map(function ($p) {
            return (string) (is_array($p) ? ($p['packageName'] ?? '') : '');
        }, $packages), $fetch);

        foreach ($packages as $i => $p) {
            if (!is_array($p)) { continue; }
            $name = (string) ($p['packageName'] ?? '');
            $packages[$i]['packageVersionCatalog'] = $p['packageVersion'] ?? null;
            $packages[$i]['packageVersion'] = $this->latestVersion($name, $p['packageVersion'] ?? null);
        }

        return $packages;
    }

    /**
     * Pré-charge la dernière version publiée pour une liste de packages, avec un cache DISQUE (TTL)
     * et une salve réseau PARALLÈLE (curl_multi) pour les entrées froides.
     *
     * Sans cache, ~24 requêtes par page rendaient la liste lente (8-12 s) ; le cache disque (TTL 1 h)
     * rend les chargements suivants instantanés et « auto met à jour » les versions dans l'heure
     * d'une release. Tout échec (module absent de packagist.org, réseau) laisse la valeur à null →
     * l'appelant retombe sur `packageVersion`.
     *
     * @param string[] $names
     * @param bool $fetch false → n'utilise QUE le cache (disque), aucune requête réseau (pour les KPI :
     *                    on ne déclenche pas ~43 appels juste pour un compteur ; la liste, elle, fetch).
     */
    public function primeLatestVersions(array $names, $fetch = true)
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
                $this->absentCache[$n]        = (bool) ($disk[$n]['absent'] ?? false);
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
            $absent = false;
            if ($code === 200 && $body !== '') {
                $this->latestVersionCache[$n] = $this->pickLatestStable($n, $body);
            } elseif ($code === 404) {
                // Réponse DÉFINITIVE de packagist.org : le package n'y existe pas / plus (retiré).
                // On ne s'appuie QUE sur un vrai 404 (pas un timeout/erreur réseau) pour ne jamais
                // masquer à tort un package quand packagist.org est momentanément indisponible.
                $absent = true;
            }
            $this->absentCache[$n] = $absent;
            // On mémorise MÊME les échecs (valeur null) pour ne pas retenter à chaque requête pendant le TTL.
            $disk[$n] = ['v' => $this->latestVersionCache[$n], 'absent' => $absent, 't' => $now];
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }
        curl_multi_close($mh);
        $this->writeVersionCacheFile($disk);

        return;
    }

    /**
     * Dernière version d'un package, LUE dans le cache de requête (rempli par primeLatestVersions),
     * avec repli sur $fallback (packageVersion). Ne déclenche AUCUNE requête réseau ici : c'est
     * l'appelant qui décide de fetcher via primeLatestVersions, pour maîtriser la latence (une salve
     * parallèle par page, pas un appel synchrone caché par package).
     */
    public function latestVersion($name, $fallback = null)
    {
        if ((string) $name === '') { return $fallback; }
        $v = $this->latestVersionCache[(string) $name] ?? null;
        return $v !== null && $v !== '' ? $v : $fallback;
    }

    /**
     * true si le package est CONFIRMÉ absent de packagist.org (404) — donc retiré/indisponible.
     * Un simple échec réseau (timeout, packagist.org down) ne remonte PAS true : on ne masque que
     * sur une réponse 404 définitive. Requiert que primeLatestVersions ait été appelé avec fetch=true.
     */
    public function isAbsentFromPackagist($name)
    {
        return $this->absentCache[(string) $name] ?? false;
    }

    /** Plus récente version STABLE d'une réponse packagist.org p2 (versions du + récent au + ancien). */
    private function pickLatestStable($name, $json)
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
            if (strpos($low, 'dev-') === 0 || strpos($low, '-dev') !== false) { continue; }
            if (preg_match('/(alpha|beta|rc)/i', $ver)) { continue; }
            return $ver;                                    // 1re version stable rencontrée
        }
        return $fallback;
    }

    private function versionCacheFilePath()
    {
        return sys_get_temp_dir() . '/melis-marketplace-latest-versions.json';
    }

    /** @return array<string,array{v:?string,absent:bool,t:int}> */
    private function readVersionCacheFile()
    {
        $f = $this->versionCacheFilePath();
        if (!is_readable($f)) { return []; }
        $data = json_decode((string) @file_get_contents($f), true);
        return is_array($data) ? $data : [];
    }

    /** @param array<string,mixed> $data */
    private function writeVersionCacheFile(array $data)
    {
        // Re-lire et fusionner avant d'écrire : liste et stats tournent en requêtes CONCURRENTES ;
        // sans fusion, la dernière écriture écraserait les entrées de l'autre et le cache ne se
        // réchaufferait jamais complètement.
        $merged = array_merge($this->readVersionCacheFile(), $data);
        @file_put_contents($this->versionCacheFilePath(), json_encode($merged), LOCK_EX);
    }

    /**
     * @return \Laminas\Mvc\Controller\Plugin\Forward
     */
    protected function forward()
    {
        /** @var \Laminas\Mvc\Controller\Plugin\Forward $forward */
        $forward = $this->getServiceManager()->get('Application')->getMvcEvent()->getTarget()->forward();
        return $forward;
    }
}
