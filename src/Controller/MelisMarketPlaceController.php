<?php

namespace MelisMarketPlace\Controller;

use Illuminate\View\View;
use Laminas\Session\Container;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Laminas\Json\Json;
use MelisCore\Controller\MelisAbstractActionController;
use PDO;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Adapter\Adapter as DbAdapter;
use Laminas\Db\Sql\Ddl;

/**
 * Class MelisMarketPlaceController
 * @package MelisMarketPlace\Controller
 */
class MelisMarketPlaceController extends MelisAbstractActionController
{
    /** @var  \Laminas\Db\Adapter\Adapter $adapter */
    protected $adapter;

    const ACTION_REQUIRE = 'require';
    const ACTION_DOWNLOAD = 'download';

    /**
     * Handles the display of the tool
     *
     * @return \Laminas\View\Model\ViewModel
     */
    public function toolContainerAction()
    {
        $url = $this->getMelisPackagistServer();
        $melisKey = $this->getMelisKey();
        $config = $this->getServiceManager()->get('MelisConfig');
        $searchForm = $config->getItem('melismarketplace_toolstree_section/forms/melis_market_place_search');

        $packageGroupData = @file_get_contents($url . '/get-package-group', true);

        try {
            $packageGroupData = Json::decode($packageGroupData, Json::TYPE_ARRAY);
        } catch (\Exception $e) {
            $packageGroupData = null;
        }

        $factory = new \Laminas\Form\Factory();
        $formElements = $this->getServiceManager()->get('FormElementManager');
        $factory->setFormElementManager($formElements);
        $searchForm = $factory->createForm($searchForm);

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $response = @file_get_contents($url . '/get-most-downloaded-packages');
        try {
            $packages = Json::decode($response, Json::TYPE_ARRAY);
        } catch (\Exception $e) {
            $packages = null;
        }

        $view = new ViewModel();

        $view->melisKey = $melisKey;
        $view->melisPackagistServer = $url;
        $view->packages = $packages;
        $view->packageGroupData = $packageGroupData;
        $view->isUpdatablePlatform = $this->allowUpdate();

        $view->setVariable('searchForm', $searchForm);

        return $view;
    }

    /**
     * Melis Packagist Server URL
     *
     * @return array
     */
    private function getMelisPackagistServer()
    {
        $env = getenv('MELIS_PLATFORM') ?: 'default';
        $config = $this->getServiceManager()->get('MelisConfig');
        $server = $config->getItem('melismarketplace_toolstree_section/datas/')['melis_packagist_server'];

        if ($server) {
            return $server;
        }
    }

    /**
     * MelisMarketPlace/src/MelisMarketPlace/Controller/MelisMarketPlaceController.php
     * Returns the melisKey of the view that is being set in app.interface
     *
     * @return string
     */
    private function getMelisKey()
    {
        $melisKey = $this->params()->fromRoute('melisKey', $this->params()->fromQuery('melisKey'), null);

        return $melisKey;
    }

    /**
     * Checking if the current Platform allows to update marketplace
     *
     * @return bool
     */
    private function allowUpdate()
    {
        $platformTable = $this->getServiceManager()->get('MelisCoreTablePlatform');
        $currentPlatform = $platformTable->getEntryByField('plf_name', getenv('MELIS_PLATFORM'))->current();

        if ($currentPlatform) {
            if ($currentPlatform->plf_update_marketplace) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handles the display of a specific package
     *
     * @return \Laminas\View\Model\ViewModel
     * @throws \Exception
     */
    public function toolContainerProductViewAction()
    {
        $url = $this->getMelisPackagistServer();
        $melisKey = $this->getMelisKey();
        $packageId = (int) $this->params()->fromQuery('packageId', null);

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $marketPlaceStatus = $this->checkStatusMarketPlace();
        $response = @file_get_contents($url . '/get-package/' . $packageId);
        try {
            $package = Json::decode($response, Json::TYPE_ARRAY);

        } catch (\Exception $e) {
            $package = null;
        }
        $isExempted = false;

        $currentVersion = null;

        //get and compare the local version from repo
        if (!empty($package)) {

            //get marketplace service
            $marketPlaceService = $this->getServiceManager()->get('MelisMarketPlaceService');
            $moduleSvc = $this->getServiceManager()->get('MelisAssetManagerModulesService');

            //compare the package local version to the repository
            if (isset($package['packageModuleName'])) {

                $module = $package['packageModuleName'];
                $version = $marketPlaceService->compareLocalVersionFromRepo($package['packageModuleName'], $package['packageVersion']);

                if (!empty($d)) {
                    $package['version_status'] = $this->getVersionStatusText($version);
                } else {
                    $package['version_status'] = "";
                }

                if (in_array($module, $this->getModuleExceptions())) {
                    $isExempted = true;
                }
            }
        }

        $response = @file_get_contents($url . '/get-most-downloaded-packages');
        try {
            $packages = Json::decode($response, Json::TYPE_ARRAY);
        } catch (\Exception $e) {
            $packages = null;
        }

        $isModuleInstalled = (bool) $this->isModuleInstalled($package['packageModuleName']);

        if ($isModuleInstalled) {
            $currentVersion = $moduleSvc->getModulesAndVersions($module)['version'];
        }

        $view = new ViewModel();
        $view->melisKey = $melisKey;
        $view->packageId = $packageId;
        $view->package = $package;
        $view->packages = $packages;
        $view->isModuleInstalled = $isModuleInstalled;
        $view->melisPackagistServer = $url;
        $view->isExempted = $isExempted;
        $view->versionStatus = $version;
        $view->versionText = $this->getVersionStatusText($version);
        $view->isUpdatablePlatform = $this->allowUpdate();
        $view->currentVersion = $currentVersion;
        $view->marketPlaceStatus = $marketPlaceStatus;

        return $view;
    }

    /**
     * @return bool|null
     */
    private function checkStatusMarketPlace()
    {
        //Table
        $platformTbl = $this->getServiceManager()->get('MelisCoreTablePlatform');
        //Get the current Env
        $currentEnv = getenv('MELIS_PLATFORM');
        try {
            $marketPlaceStatus = $platformTbl->getEntryByField('plf_name', $currentEnv)->current();

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $marketPlaceStatus->plf_update_marketplace ?? null;
    }

    /**
     * @param $status
     *
     * @return string
     */
    private function getVersionStatusText($status)
    {
        $service = $this->getMarketPlaceService();

        switch ($status) {
            case $service::NEED_UPDATE:
                return $this->getTool()->getTranslation('tr_market_place_version_update');
                break;
            case $service::UP_TO_DATE:
                return $this->getTool()->getTranslation('tr_market_place_version_up_to_date');
                break;
            case $service::IN_ADVANCE:
                return $this->getTool()->getTranslation('tr_market_place_version_in_advance');
                break;
        }
    }

    /**
     * @return \MelisMarketPlace\Service\MelisMarketPlaceService
     */
    private function getMarketPlaceService()
    {
        /** @var \MelisMarketPlace\Service\MelisMarketPlaceService $service */
        $service = $this->getServiceManager()->get('MelisMarketPlaceService');
        return $service;
    }

    /**
     * @return \MelisCore\Service\MelisCoreToolService
     */
    private function getTool()
    {
        /** @var \MelisCore\Service\MelisCoreToolService $tool */
        $tool = $this->getServiceManager()->get('MelisCoreTool');
        return $tool;
    }

    /**
     * Returns the list of modules that is inside the exceptions array
     *
     * @return array
     */
    private function getModuleExceptions()
    {
        $env = getenv('MELIS_PLATFORM') ?: 'default';
        $config = $this->getServiceManager()->get('MelisConfig');
        $modules = $config->getItem('melismarketplace_toolstree_section/datas/')['exceptions'];

        if ($modules) {
            return $modules;
        }
    }

    /**
     * Checks if the module is installed or not
     *
     * @param $module
     *
     * @return bool
     */
    private function isModuleInstalled($module)
    {
        if ($this->getServiceManager()->get('MelisAssetManagerModulesService')->getModulePath($module)) {
            return true;
        }

        return false;
    }

    /**
     * Translates the retrieved data coming from the Melis Packagist URL
     * and transform's it into a display including the pagination
     *
     * @return \Laminas\View\Model\ViewModel
     */
    public function moduleListAction()
    {
        $packages = [];
        $itemCountPerPage = 1;
        $pageCount = 1;
        $currentPageNumber = 1;
        $pagination = null;

        if ($this->getRequest()->isPost()) {

            /*
             *  For verifying the moduleList
             */
            $config = $this->getServiceManager()->get('MelisConfig');
            $searchForm = $config->getItem('melismarketplace_toolstree_section/forms/melis_market_place_search');

            //end verifying modules

            $factory = new \Laminas\Form\Factory();
            $formElements = $this->getServiceManager()->get('FormElementManager');
            $factory->setFormElementManager($formElements);
            $searchForm = $factory->createForm($searchForm);

            $post = $this->getTool()->sanitizeRecursive(get_object_vars($this->getRequest()->getPost()), [], true);
            //get only modules that are not bundle
            $post['bundle'] = 0;
            //get packages
            $serverPackages = $this->fetchPackages($post);

            $packages = isset($serverPackages['packages']) ? $serverPackages['packages'] : null;
            $itemCountPerPage = isset($serverPackages['itemCountPerPage']) ? $serverPackages['itemCountPerPage'] : null;
            $pageCount = isset($serverPackages['pageCount']) ? $serverPackages['pageCount'] : null;
            $currentPageNumber = isset($serverPackages['currentPageNumber']) ? $serverPackages['currentPageNumber'] : null;
            $pagination = isset($serverPackages['pagination']) ? $serverPackages['pagination'] : null;

        }

        $view = new ViewModel();

        $view->setTerminal(true);

        $view->packages = $packages;
        $view->itemCountPerPage = $itemCountPerPage;
        $view->pageCount = $pageCount;
        $view->currentPageNumber = $currentPageNumber;
        $view->pagination = $pagination;
        $view->isUpdatablePlatform = $this->allowUpdate();
        $view->setVariable('searchForm', $searchForm);

        return $view;
    }

    /**
     * Translates the retrieved data coming from the Melis Packagist URL
     * and transform's it into a display including the pagination
     *
     * @return \Laminas\View\Model\ViewModel
     */
    public function bundleListAction()
    {
        $bundles = [];
        $itemCountPerPage = 1;
        $pageCount = 1;
        $currentPageNumber = 1;
        $pagination = null;
        $isBundleOnly = false;

        if ($this->getRequest()->isPost()) {
            $post = $this->getTool()->sanitizeRecursive(get_object_vars($this->getRequest()->getPost()), [], true);

            if($post['bundle'] == 1)
                $isBundleOnly = true;
            else
                $post['itemPerPage'] = 3;//limit the bundle if modules list is also displayed

            //get only modules that are bundle
            $post['bundle'] = 1;

            //get bundle list
            $serverPackages = $this->fetchPackages($post);

            $bundles = isset($serverPackages['packages']) ? $serverPackages['packages'] : null;
            $itemCountPerPage = isset($serverPackages['itemCountPerPage']) ? $serverPackages['itemCountPerPage'] : null;
            $pageCount = isset($serverPackages['pageCount']) ? $serverPackages['pageCount'] : null;
            $currentPageNumber = isset($serverPackages['currentPageNumber']) ? $serverPackages['currentPageNumber'] : null;
            $pagination = isset($serverPackages['pagination']) ? $serverPackages['pagination'] : null;

        }

        $view = new ViewModel();

        $view->setTerminal(true);

        $view->bundles = $bundles;
        $view->isBundleOnly = $isBundleOnly;
        $view->itemCountPerPage = $itemCountPerPage;
        $view->pageCount = $pageCount;
        $view->currentPageNumber = $currentPageNumber;
        $view->pagination = $pagination;
        $view->isUpdatablePlatform = $this->allowUpdate();

        return $view;
    }

    /**
     * @param $post
     * @return false|mixed|string|null
     */
    private function fetchPackages($post)
    {
        $page = isset($post['page']) ? (int) $post['page'] : 1;
        $search = isset($post['search']) ? $post['search'] : '';
        $orderBy = isset($post['orderBy']) ? $post['orderBy'] : 'mp_total_downloads';
        $order = isset($post['order']) ? $post['order'] : 'desc';
        $itemPerPage = isset($post['itemPerPage']) ? (int) $post['itemPerPage'] : 8;
        $group = isset($this->getRequest()->getQuery()['group']) ? (string) $this->getRequest()->getQuery()['group'] : null;
        $bundle = isset($post['bundle']) ? $post['bundle'] : null;


        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $search = urlencode($search);
        $requestJsonUrl = $this->getMelisPackagistServer() . '/get-packages/page/' . $page . '/search/' . $search
            . '/item_per_page/' . $itemPerPage . '/order/' . $order . '/order_by/' . $orderBy . '/status/1' . '/group/' . $group
            . '/bundle/' . $bundle;

        $serverPackages = @file_get_contents($requestJsonUrl);
        try {
            $serverPackages = Json::decode($serverPackages, Json::TYPE_ARRAY);
        } catch (\Exception $e) {
            $serverPackages = null;
        }
        $tmpPackages = empty($serverPackages['packages']) ?: $serverPackages['packages'];


        if (isset($serverPackages['packages']) && $serverPackages['packages']) {
            // check if the module is installed
            $installedModules = $this->getServiceManager()->get('MelisAssetManagerModulesService')->getAllModules();
            $installedModules = array_map(function ($a) {
                return trim(strtolower($a));
            }, $installedModules);


            // rewrite array, add installed status
            foreach ($serverPackages['packages'] as $idx => $package) {
                $isInstalled = false;
                // to make sure it will match
                $packageName = trim(strtolower($package['packageModuleName']));

                if (in_array($packageName, $installedModules)) {
                    $tmpPackages[$idx]['installed'] = true;
                } else {
                    $tmpPackages[$idx]['installed'] = false;
                }

                $isInstalled = (bool) $tmpPackages[$idx]['installed'];

                //compare the package local version to the repository
                if (isset($tmpPackages[$idx]['packageModuleName'])) {
                    $d = $this->getMarketPlaceService()->compareLocalVersionFromRepo($tmpPackages[$idx]['packageModuleName'], $tmpPackages[$idx]['packageVersion']);

                    if (!empty($d)) {
                        $tmpPackages[$idx]['version_status'] = $isInstalled === true ? $this->getVersionStatusText($d) : "";
                    } else {
                        $tmpPackages[$idx]['version_status'] = "";
                    }
                }
            }

            $serverPackages['packages'] = $tmpPackages;
        }
        return $serverPackages;
    }

    /**
     * @return \Laminas\View\Model\ViewModel
     */
    public function toolProductModalContainerAction()
    {
        $id = $this->getTool()->sanitize($this->params()->fromRoute('id', $this->params()->fromQuery('id', '')));
        $melisKey = $this->params()->fromRoute('melisKey', $this->params()->fromQuery('melisKey', ''));

        $view = new ViewModel();
        $view->setTerminal(true);
        $view->id = $id;
        $view->melisKey = $melisKey;

        return $view;
    }

    /**
     * @return \Laminas\View\Model\ViewModel
     */
    public function toolProductModalContentAction()
    {
        $module = $this->getTool()->sanitize($this->params()->fromQuery('module', ''));
        $action = $this->getTool()->sanitize($this->params()->fromQuery('action', ''));

        $melisKey = $this->params()->fromRoute('melisKey', '');
        $title = $this->getTool()->getTranslation('tr_market_place_' . $action) . ' ' . $module;
        $data = [];
        $status = '';
        $composerSvc = $this->getServiceManager()->get('MelisComposerService');

        switch ($action) {
            case $composerSvc::DOWNLOAD:
                $status = 'Downloading...';
                break;
            case $composerSvc::UPDATE:
                $status = 'Updating...';
                break;
            case $composerSvc::REMOVE:
                $status = 'Removing...';
                break;
        }

        $view = new ViewModel();

        $view->melisKey = $melisKey;
        $view->title = $title;
        $view->module = $module;
        $view->status = $status;

        return $view;
    }

    /**
     * @return \Laminas\View\Model\ViewModel
     */
    public function toolModuleFormSetupContentAction()
    {
        $module = $this->getTool()->sanitize($this->params()->fromQuery('module', ''));
        $action = $this->getTool()->sanitize($this->params()->fromQuery('action', ''));
        $melisKey = $this->params()->fromRoute('melisKey', '');

        $title = $this->getTool()->getTranslation('tr_melis_marketplace_setup_module_modal_title', [$module]);
        $form = $this->getMarketPlaceService()->getForm($module);

        $view = new ViewModel();
        $view->melisKey = $melisKey;
        $view->title = $title;
        $view->module = $module;
        $view->form = $form;
        $view->action = $action;

        return $view;
    }

    /**
     * @return \Laminas\View\Model\ViewModel
     */
    public function melisMarketPlaceProductDoAction()
    {

        $success = 0;
        $message = 'melis_market_place_tool_package_do_event_message_ko';
        $errors = [];
        $request = $this->getRequest();
        $title = 'tr_market_place';
        $post = [];

        if ($request->isPost()) {

            $moduleSvc = $this->getServiceManager()->get('MelisAssetManagerModulesService');
            $post = $this->getTool()->sanitizeRecursive($request->getPost()->toArray());

            $this->getEventManager()->trigger('melis_marketplace_product_do_start', $this, $post);

            $action = isset($post['action']) ? $post['action'] : '';
            $package = isset($post['package']) ? $post['package'] : '';
            $module = isset($post['module']) ? $post['module'] : '';

            if ($action && $package && $module) {

                $title = $this->getTool()->getTranslation('tr_market_place_' . $action) . ' ' . $module;
                $composerSvc = $this->getServiceManager()->get('MelisComposerService');

                switch ($action) {
                    case $composerSvc::DOWNLOAD:
                        if (!in_array($module, $this->getModuleExceptions())) {
                            /**
                             * @todo if the package has a type of "melisplatform-site"
                             * then it should use the \MelisMarketPlace\Service\MelisMarketPlaceSiteInstallService
                             * else, then use the regular composer download
                             */
                            $composerSvc->download($package);
                        }
                        break;
                    case $composerSvc::UPDATE:
                        $composerSvc->download($package);
                        break;
                    case $composerSvc::REMOVE:
                        if (!in_array($module, $this->getModuleExceptions())) {

                            // Retrieve current activated modules
                            $mm = $this->getServiceManager()->get('ModuleManager');
                            $currentModules = $mm->getLoadedModules();

                            // Unset module target module
                            if (isset($currentModules[$module]))
                                unset($currentModules[$module]);

                            $currentModules = array_keys($currentModules);

                            // Target module dependencies
                            $moduleDep = $moduleSvc->getDependencies($module);

                            /**
                             * Checking target module dependencies from other
                             * activated modules, this will avoid deactivation if
                             * on of the activated module is using
                             */
                            $tempToBeRemove = [];

                            foreach ($moduleDep as $depMod) {
                                $hasDep = false;
                                foreach ($currentModules as $cMod) {
                                    /**
                                     * Not checking in the same modules
                                     * not same as the target module
                                     */
                                    if ($depMod != $cMod && $cMod !== $module) {
                                        $modDeps = $moduleSvc->getDependencies($cMod);

                                        if (in_array($depMod, $modDeps)) {
                                            $hasDep = true;
                                            break;
                                        }
                                    }
                                }

                                /**
                                 * 1 Target module dependency module has no dependencies to other activated modules
                                 * 2 Avoid element duplication
                                 * 3 IT must on the current modules
                                 */
                                if (!$hasDep && !in_array($depMod, $tempToBeRemove) && in_array($depMod, $currentModules)) {
                                    $tempToBeRemove[] = $depMod;
                                }
                            }

                            /**
                             * Checking if the Target module dependencies
                             * listed on the root composer.json
                             * this will skip from deactivating
                             */
                            $composerJsonFile = $_SERVER['DOCUMENT_ROOT'] . '/../composer.json';
                            $composerJson = json_decode(@file_get_contents($composerJsonFile), true);
                            $composerReqs = $composerJson['require'];

                            foreach ($tempToBeRemove As $tMod){
                                // Retrieving module composer.json to get na package name
                                $moduleJson = json_decode(@file_get_contents($moduleSvc->getModulePath($tMod) . '/composer.json'), true);
                                $modulePackageName = $moduleJson['name'];

                                // Not exist on the root composer.json
                                if (!isset($composerReqs[$modulePackageName]))
                                    unset($currentModules[array_search($tMod, $currentModules)]);
                            }

                            // Re-creating module.load
                            $moduleSvc->createModuleLoader('config/', $currentModules, []);
                            $composerSvc->remove($package);
                            // $composerSvc->dumpAutoload();
                        }
                        break;
                    default:
                        echo $this->getTool()->getTranslation($message);
                        break;
                }
            }
        }

        $response = [
            'success' => $success,
            'title' => $this->getTool()->getTranslation($title),
            'message' => $this->getTool()->getTranslation($message),
            'errors' => $errors,
            'post' => $post,
        ];

        // add to flash messenger
        $this->getEventManager()->trigger('melis_marketplace_product_do_finish', $this, $response);

        $view = new ViewModel();
        $view->setTerminal(true);

        return $view;
    }

    /**
     * @param $dirPath
     *
     * @return bool
     */
    protected function hasDirRights($dirPath)
    {
        if (!file_exists($dirPath)) {
            return false;
        }

        if (is_writable($dirPath) && is_readable($dirPath)) {
            return true;
        }

        return false;
    }

    /**
     * @param $dirPath
     */
    protected function deleteDir($dirPath)
    {
        if (is_dir($dirPath)) {
            $objects = scandir($dirPath);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (!is_writable($dirPath . "/" . $object)) {
                        chmod($dirPath . "/" . $object, 0777);
                    }
                    if (filetype($dirPath . "/" . $object) == "dir") {
                        $this->deleteDir($dirPath . "/" . $object);
                    } else {
                        unlink($dirPath . "/" . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dirPath);
        }
    }

    /**
     * @return \Laminas\View\Model\JsonModel
     */
    public function isPackageDirectoryRemovableAction()
    {
        $success = 0;
        $message = 'tr_melis_marketplace_package_directory_removable_ko';
        $request = $this->getRequest();
        $title = 'tr_market_place';
        $module = 'Package';
        if ($request->isPost()) {

            $post = $this->getTool()->sanitizeRecursive($request->getPost()->toArray());
            $moduleSvc = $this->getServiceManager()->get('MelisAssetManagerModulesService');
            $module = isset($post['module']) ? $post['module'] : '';
            $modulePath = $moduleSvc->getModulePath($module);

            if ($this->hasDirRights($modulePath)) {
                $success = 1;
                $message = 'tr_melis_marketplace_package_directory_removable_ok';
            }

        }

        $response = [
            'success' => $success,
            'title' => $this->getTool()->getTranslation($title),
            'message' => $this->getTool()->getTranslation($message, $module),
        ];

        return new JsonModel($response);
    }

    /**
     * @return \Laminas\View\Model\JsonModel
     */
    public function changePackageDirectoryPermissionAction()
    {
        $success = 0;
        $message = 'tr_melis_marketplace_package_directory_change_permission_ko';
        $request = $this->getRequest();
        $title = 'tr_market_place';
        $module = 'Package';
        if ($request->isPost()) {

            $post = $this->getTool()->sanitizeRecursive($request->getPost()->toArray());
            $moduleSvc = $this->getServiceManager()->get('MelisAssetManagerModulesService');
            $module = isset($post['module']) ? $post['module'] : '';
            $modulePath = $moduleSvc->getModulePath($module);

            chmod($modulePath, 0777);

            if ($this->hasDirRights($modulePath)) {
                $success = 1;
                $message = 'tr_melis_marketplace_package_directory_change_permission_ko';
            }

        }

        $response = [
            'success' => $success,
            'title' => $this->getTool()->getTranslation($title),
            'message' => $this->getTool()->getTranslation($message, $module),
        ];

        return new JsonModel($response);
    }

    /**
     * @return \Laminas\View\Model\JsonModel
     */
    public function activateModuleAction()
    {
        $success = 0;
        $request = $this->getRequest();

        if ($request->isPost()) {
            $module = $this->getTool()->sanitize($request->getPost('module'));
            $moduleSvc = $this->getServiceManager()->get('MelisAssetManagerModulesService');
            $activeModules = $moduleSvc->getActiveModules();

            // Melis Modules required
            $arrayDependency = $this->packageRequire($module);
            //check if module is laminas module
            if($this->isLaminasModule($module))
                //include the module
                array_push($arrayDependency, $module);

            // since we are still running the function, we cannot get the accurate modules that are being loaded
            // instead, we can read the module.load
            $moduleLoadFile = $_SERVER['DOCUMENT_ROOT'] . '/../config/melis.module.load.php';

            if (file_exists($moduleLoadFile)) {

                $modules = require $moduleLoadFile;

                /**
                 * Process module activation
                 */
                foreach($arrayDependency as $mod) {
                    if($this->isLaminasModule($mod)){
                        if (!in_array($mod, $activeModules)) {
                            $moduleCount = count($modules);
                            $insertAtIdx = $moduleCount - 1;
                            array_splice($modules, $insertAtIdx, 0, $mod);
                        }
                    }
                }

                // create the module.load file
                $moduleSvc->createModuleLoader('config/', $modules, [], []);
                $success = 1;
            }
        }

        $response = [
            'success' => $success,
            'modules' => $arrayDependency
        ];

        return new JsonModel($response);
    }

    /**
     * @return \Laminas\View\Model\JsonModel
     */
    public function isModuleExistsAction()
    {
        $isExist = 0;
        $module = '';
        $request = $this->getRequest();

        if ($request->isPost()) {
            $module = $request->getPost('module');

            if ($module) {
                $isExist = (bool) $this->isModuleInstalled($module);
            }
        }

        $response = [
            'module' => $module,
            'isExist' => $isExist,
        ];

        return new JsonModel($response);

    }

    /**
     * @param $module
     * @return bool
     */
    public function isLaminasModule($module)
    {
        $laminasModule = false;

        $moduleSrc = $this->getServiceManager()->get('MelisAssetManagerModulesService');
        $repos = $moduleSrc->getComposer()->getRepositoryManager()->getLocalRepository();
        $packageName = $this->convertToPackageName($module);
        $packageInfo = $repos->findPackages("melisplatform/".$packageName);
        /**
         * Check if package exist
         */
        if(!empty($packageInfo[0])){
            /**
             * Check if package is melis platform module
             */
            if($packageInfo[0]->getType() == 'melisplatform-module') {
                $extra = $packageInfo[0]->getExtra();
                /**
                 * Check if module is laminas module
                 * or form other framework(laravel,symfony,lumen,silex)
                 *
                 * If melis-module key does not exist,
                 * then we expect it is a laminas module
                 */
                if (isset($extra['melis-module'])) {
                    if($extra['melis-module']){
                        /**
                         * Module is made in laminas module
                         */
                        $laminasModule = true;
                    }else{
                        /**
                         * Module is made form other framework
                         */
                        $laminasModule = false;
                    }
                } else {
                    /**
                     * Module is made in laminas module
                     */
                    $laminasModule = true;
                }
            }
        }

        return $laminasModule;
    }

    /**
     * Convert module name to package name: (MelisCore -> melis-core)
     * @param $module
     * @return string
     */
    public function convertToPackageName($module)
    {
        $moduleName = strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $module));

        return $moduleName;
    }

    /**
     * @return \Laminas\View\Model\JsonModel
     */
    public function getModuleTablesAction()
    {
        $module = $this->getTool()->sanitize($this->getRequest()->getPost('module'));
        $tables = [];
        $files = [];

        if ($this->getRequest()->isPost()) {
            $svc = $this->getServiceManager()->get('MelisAssetManagerModulesService');
            $path = $svc->getModulePath($module, true);
            $dbDeployPath = $path . '/install/dbdeploy/';
            $tableInstall = '.sql';
            $dbDeployFile = null;

            // look for setup_structure SQL file

            if (file_exists($dbDeployPath . $dbDeployFile)) {
                $dbDeployFiles = array_diff(scandir($dbDeployPath), ['.', '..', '.gitignore']);

                if ($dbDeployFiles) {
                    foreach ($dbDeployFiles as $file) {
                        $files[] = $file;
                        if (strrpos($file, $tableInstall) !== false) {
                            $dbDeployFile = $file;
                        }
                    }
                }

                $dbDeployFile = $dbDeployPath . $dbDeployFile;

                set_time_limit(0);
                ini_set('memory_limit', '-1');

                $dbDeployFile = @file_get_contents($dbDeployFile);
                if (preg_match_all('/CREATE\sTABLE\sIF\sNOT\sEXISTS\s\`(.*?)+\`/', $dbDeployFile, $matches)) {
                    $tables = isset($matches[0]) ? $matches[0] : null;
                    $tables = array_map(function ($a) {
                        $n = str_replace(['CREATE TABLE IF NOT EXISTS', '`'], '', $a);
                        $n = trim($n);

                        return $n;
                    }, $tables);

                    sort($tables);
                    sort($files);

                }
            }
        }

        return new JsonModel([
            'module' => $module,
            'tables' => $tables,
            'files' => $files,
        ]);
    }

    /**
     * @return \Laminas\View\Model\JsonModel|\Laminas\View\Model\ViewModel
     */
    public function exportTablesAction()
    {
        $module = $this->getTool()->sanitize($this->getRequest()->getPost('module'));
        $tables = $this->getTool()->sanitize($this->getRequest()->getPost('tables'));
        $files = $this->getTool()->sanitize($this->getRequest()->getPost('files'));

        $success = 0;
        $message = $this->getTool()->getTranslation('tr_melis_market_place_export_table_empty');
        $response = $this->getResponse();
        if ($module) {

            $sql = '';
            $insert = "INSERT INTO `%s`(%s) VALUES(%s);" . PHP_EOL;
            $dumpInfo = "\n--\n-- Dumping data for table `%s`\n--\n";
            $copyright = "-- Melis Platform SQL Dump\n-- https://www.melistechnology.com\n";
            $commit = "\nCOMMIT;";
            $columns = "";
            $values = "";
            $export = "";

            set_time_limit(0);
            ini_set('memory_limit', -1);

            // check again if the tables are not empty
            if (is_array($tables)) {

                // trim the matched texts
                $tables = array_map(function ($a) {
                    $n = trim($a);

                    return $n;
                }, $tables);

                $adapter = $this->getAdapter();

                if ($this->getAdapter()) {

                    $dropQueryTable = "";
                    foreach ($tables as $table) {
                        try {
                            $resultSet = $adapter->query("SELECT * FROM `$table`", DbAdapter::QUERY_MODE_EXECUTE)->toArray();
                            if ($resultSet) {
                                // CREATE AN INSERT SQL FILE
                                $sql .= sprintf($dumpInfo, $table);
                                foreach ($resultSet as $data) {

                                    // clear columns and values every loop
                                    $columns = '';
                                    $values = '';

                                    foreach ($data as $column => $value) {
                                        $columns .= "`$column`, ";

                                        if (is_numeric($value) || $value == '0') {
                                            $values .= "$value, ";
                                        } else {
                                            if (is_null($value)) {
                                                $values .= "NULL, ";
                                            } else {
                                                $values .= "'$value', ";
                                            }
                                        }
                                    }

                                    $columns = substr($columns, 0, strlen($columns) - 2);
                                    $values = substr($values, 0, strlen($values) - 2);
                                    $sql .= sprintf($insert, $table, $columns, $values);
                                }
                            }

                            $dropQueryTable .= "DROP TABLE IF EXISTS `{$table}`;" . PHP_EOL;
                        } catch (\PDOException $e) {
                            $message .= ' ' . PHP_EOL . $e->getMessage() . PHP_EOL;
                        }
                    }

                    if ($dropQueryTable) {
                        // execute drop table
                        $adapter->query($dropQueryTable, DbAdapter::QUERY_MODE_EXECUTE);
                    }

                    // delete the dbdeploy file in the changelog table
                    if ($files) {
                        $dbDeployQuery = "";
                        foreach ($files as $file) {
                            $dbDeployQuery .= "DELETE FROM `changelog` where `description` = '" . $file . "';";
                            $dbDeployFileCache = $_SERVER['DOCUMENT_ROOT'] . '/../dbdeploy/data/' . $file;
                            if (file_exists($dbDeployFileCache)) {
                                unlink($dbDeployFileCache);
                            }
                        }

                        if ($dbDeployQuery) {
                            $adapter->query($dbDeployQuery, DbAdapter::QUERY_MODE_EXECUTE);
                        }
                    }

                    if ($sql) {
                        $success = 1;
                    }
                }
            }

            if ($success) {
                $export = $copyright . $sql . $commit;
                $fileName = strtolower($module) . '_export_data.sql';

                $response->getHeaders()
                    ->addHeaderLine('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0')
                    ->addHeaderLine('Content-Disposition', 'attachment; filename="' . $fileName)
                    ->addHeaderLine('Content-Length', strlen($export))
                    ->addHeaderLine('Pragma', 'no-cache')
                    ->addHeaderLine('Content-Type', 'application/sql;charset=UTF-8')
                    ->addHeaderLine('error', '0')
                    ->addHeaderLine('fileName', $fileName);

                $response->setContent($export);
                $response->setStatusCode(200);

                $view = new ViewModel();
                $view->setTerminal(true);

                $view->content = $response->getContent();

                return $view;
            }
        }

        if (!$success) {
            $response->getHeaders()->addHeaderLine("error", 1);

            return new JsonModel([
                'success' => $success,
                'message' => $message,
            ]);
        }
    }

    /**
     * Returns the instance of DbAdapter
     *
     * @return \Laminas\Db\Adapter\Adapter
     */
    private function getAdapter()
    {
        $this->setDbAdapter();

        return $this->adapter;
    }

    /**
     * @inheritdoc
     * Sets the Database adapter that will be used when querying
     * the database, this will use the configuration set
     * on the database config file
     */
    private function setDbAdapter()
    {
        // access the database configuration
        $config = $this->getServiceManager()->get('config');
        $db = $config['db'];

        if ($db)
            $this->adapter = new DbAdapter($db);

        return $this;
    }

    /**
     * This method will return required moduel of requested module
     *
     * @param $module
     * @return array
     */
    public function packageRequire($module)
    {
        $psr4 = 'psr-4';

        $composerFile = $_SERVER['DOCUMENT_ROOT'] . '/../vendor/composer/installed.json';
        $composerInstalledPckg = (array) \Laminas\Json\Json::decode(file_get_contents($composerFile));

        $packageRequire = array();
        foreach ($composerInstalledPckg As $pckgConfg){

            if (!empty($pckgConfg->autoload->$psr4)){
                $moduleName = null;
                foreach ($pckgConfg->autoload->$psr4 As $modName => $v)
                    $moduleName = $modName;

                $moduleName = rtrim($moduleName, '\\');

                if ($moduleName === $module){
                    if (!empty($pckgConfg->require)){
                        foreach ($pckgConfg->require As $pckgName => $v){
                            if (!is_bool(strpos($pckgName, 'melisplatform'))){

                                foreach ($composerInstalledPckg As $reqPckg => $reqPckgConf){

                                    if ($reqPckgConf->name == $pckgName){

                                        $moduleName = $pckgName;
                                        foreach ($reqPckgConf->autoload->$psr4 As $modName => $v)
                                            $moduleName = $modName;

                                        $moduleName = rtrim($moduleName, '\\');

                                        // Skipping not modules of Melis Platform
                                        // e.g MelisPlatformFrameworkLaravel, MelisPlatformFrameworkSymfony etc...
                                        $xtraModuleName = 'module-name';
                                        if (!empty($reqPckgConf->extra->$xtraModuleName))
                                            array_push($packageRequire, $moduleName);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $moduleMngr = $this->getServiceManager()->get('ModuleManager');
        $activeModules = array_keys($moduleMngr->getLoadedModules());

        if (!empty($packageRequire))
            foreach ($packageRequire As $key => $reqMod)
                if (in_array($reqMod, $activeModules))
                    unset($packageRequire[$key]);



        return $packageRequire;
    }

    /**frontIdPage
     * @return \Laminas\View\Model\JsonModel
     */
    public function execDbDeployAction()
    {
        $success = false;
        $request = $this->getRequest();

        if ($request->isPost()) {
            $module = $this->getTool()->sanitize($request->getPost('module'));
            if ($module) {

                $modules[] = $module;

                $moduleDpndncs = $this->packageRequire($module);
                if (!empty($moduleDpndncs))
                    $modules = array_merge($moduleDpndncs, $modules);

                /** @var \MelisDbDeploy\Service\MelisDbDeployDiscoveryService $deployDiscoveryService */
                $deployDiscoveryService = $this->getServiceManager()->get('MelisDbDeployDiscoveryService');

                foreach ($modules as $key => $module) {
                    $deployDiscoveryService->processing($module);
                }

                if ($this->reprocessDbDeploy()) {
                    $success = true;
                }else{
                    $success = -1;
                }
            }
        }

        return new JsonModel([
            'success' => $success,
        ]);
    }

    private function reprocessDbDeploy()
    {
        $service = new \MelisDbDeploy\Service\MelisDbDeployDeployService();

        if (false === $service->isInstalled()) {
            $service->install();
        }

        if ($service->changeLogCount() === $this->getTotalDataFile()) {
            return true;
        }

        return false;
    }

    private function getTotalDataFile()
    {
        $dbDeployPath = $_SERVER['DOCUMENT_ROOT'] . '/../dbdeploy/data/';

        if (!file_exists($dbDeployPath)) {
            return 0;
        }

        $files = glob($dbDeployPath . '*.sql');

        return count($files);
    }

    /**
     * dashboard view of market place
     *
     * @return \Laminas\View\Model\ViewModel
     */
    public function marketPlaceDashboardAction()
    {
        $url = $this->getMelisPackagistServer() . "/get-most-downloaded-packages";
        $melisKey = $this->getmelisKey();
        $moduleService = $this->getServiceManager()->get('MelisAssetManagerModulesService');
        $data = [];
        $downloadedmodulesData = [];
        $packages = [];

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $downloadedmodulesData = @file_get_contents($url);
        try {
            $packages = json_decode($downloadedmodulesData, true);
        } catch (\Exception $e) {
            $packages = null;
        }

        $moduleList = $moduleService->getAllModules();

        $request = $this->getRequest();
        $uri = $request->getUri();
        $domain = $uri->getHost();
        $scheme = $uri->getScheme();

        if (isset($packages['packages'])) {
            foreach ($packages['packages'] as $packagesData => $packagesValue) {
                $data[] = [
                    'packageId' => $packagesValue['packageId'],
                    'packageTitle' => $packagesValue['packageTitle'],
                    'packageName' => $packagesValue['packageName'],
                    'packageSubtitle' => $packagesValue['packageSubtitle'],
                    'packageModuleName' => $packagesValue['packageModuleName'],
                    'packageDescription' => $packagesValue['packageDescription'],
                    'packageImages' => isset($packagesValue['packageImages'][0]) ? $packagesValue['packageImages'][0] : null,
                    'packageUrl' => $packagesValue['packageUrl'],
                    'packageRepository' => $packagesValue['packageRepository'],
                    'packageTotalDownloads' => $packagesValue['packageTotalDownloads'],
                    'packageVersion' => $packagesValue['packageVersion'],
                    'packageTimeOfRelease' => $packagesValue['packageTimeOfRelease'],
                    'packageMaintainers' => $packagesValue['packageMaintainers'],
                    'packageType' => $packagesValue['packageType'],
                    'packageDateAdded' => $packagesValue['packageDateAdded'],
                    'packageLastUpdate' => $packagesValue['packageLastUpdate'],
                    'packageGroupId' => $packagesValue['packageGroupId'],
                    'packageGroupName' => $packagesValue['packageGroupName'],
                    'packageIsActive' => $packagesValue['packageIsActive'],
                ];
            }
        }

        $view = new ViewModel();

        $view->melisKey = $melisKey;
        $view->modules = serialize($moduleList);
        $view->scheme = $scheme;
        $view->domain = $domain;

        $view->downloadedPackages = $data;

        return $view;
    }

    public function reDumpAutoloadAction()
    {
        $composerSvc = $this->getServiceManager()->get('MelisComposerService');
        $composerSvc->dumpAutoload();
        exit;
    }

    public function executeComposerScriptsAction()
    {
        \MelisCore\ModuleComposerScript::setServiceManager($this->getServiceManager());
        \MelisCore\ModuleComposerScript::executeScripts();

        $view = new ViewModel();
        $view->setTerminal(true);
        return $view;
    }

    /**
     * @return \Laminas\View\Model\ViewModel
     */
    public function marketPlaceModuleHeaderAction()
    {
        $melisKey = $this->getMelisKey();

        $moduleService = $this->getServiceManager()->get('MelisAssetManagerModulesService');
        $marketplaceService = $this->getServiceManager()->get('MelisMarketPlaceService');
        $requestJsonUrl = $this->getMelisPackagistServer() . '/get-packages/page/1/search//item_per_page/0/order/asc/order_by//status/2/group/';
        $serverPackages = [];
        $packagesData = [];
        $tmpData = [];
        $excludedModules = [
            'MelisAssetManager',
            'MelisComposerDeploy',
            'MelisDbDeploy',
            'MelisInstaller',
            'MelisCore',
            'MelisEngine',
            'MelisFront',
        ];

        $serverPackages = @file_get_contents($requestJsonUrl);
        try {
            $serverPackages = json_decode($serverPackages, true);
        } catch (\Exception $e) {
            $serverPackages = null;
        }

        //Get the all latest packages
        if (is_array($serverPackages)) {
            foreach ($serverPackages['packages'] as $packagist => $packageVal) {
                $tmpData[] = [
                    'packageId' => $packageVal['packageId'],
                    'packageModuleName' => $packageVal['packageTitle'],
                    'latestVersion' => $packageVal['packageVersion'],
                    'packageName' => $packageVal['packageName'],
                    'groupName' => $packageVal['packageGroupName'],
                ];

                array_push($packagesData, $packageVal['packageModuleName']);
            }
        }

        /*
         * verify modules of their current versions
         */
        $moduleList = $moduleService->getVendorModules();
        $moduleList = array_diff($moduleList, $excludedModules);

        $data = [];
        $count = 0;

        foreach ($moduleList as $module => $moduleName) {

            $version = null;
            $packageId = null;
            $packageName = null;
            $groupName = null;
            $currentVersion = null;

            for ($i = 0; $i < count($tmpData); $i++) {

                $moduleVersion = $moduleService->getModulesAndVersions($moduleName);

                if ($moduleVersion['package'] == $tmpData[$i]['packageName']) {

                    $version = $tmpData[$i]['latestVersion'];
                    $packageId = $tmpData[$i]['packageId'];
                    $packageName = $tmpData[$i]['packageModuleName'];
                    $groupName = $tmpData[$i]['groupName'];
                    $currentVersion = $moduleVersion['version'];
                }
            }
            //Get the version difference of local modules from repo modules
            $status = $marketplaceService->compareLocalVersionFromRepo($moduleName, $version);

            $data[] = [
                'module_name' => $packageName,
                'latestVersion' => $version,
                'status' => $status,
                'packageId' => $packageId,
                'groupName' => $groupName,
                'currentVersion' => $currentVersion,
            ];

            if ((int) $status == -1) {
                $count++;
            }
        }

        $view = new ViewModel();
        $view->melisKey = $melisKey;
        $view->modules = $data;
        $view->needToUpdateModuleCount = $count;

        return $view;
    }

    /**
     * @return string
     */
    private function removeMelisPackagistServer()
    {
        $env = getenv('MELIS_PLATFORM') ?: 'default';

        if ($env) {
            return $env;
        }
    }

    /**
     * @return \Laminas\View\Model\JsonModel
     */
    public function plugModuleAction()
    {
        $success = false;
        $message = $this->getTool()->getTranslation('tr_melis_market_place_plug_module_ko', ['']);

        if ($this->getRequest()->isPost()) {
            $module = $this->getRequest()->getPost('module');
            if ($module) {

                // Retrieve current activated modules
                $mm = $this->getServiceManager()->get('ModuleManager');
                $currentModules = $mm->getLoadedModules();

                //include module in the activation if it is laminas module
                if($this->isLaminasModule($module))
                    $modules[] = $module;
                else
                    $modules = [];

                $moduleDpndncs = $this->packageRequire($module);

                if (!empty($moduleDpndncs))
                    foreach ($moduleDpndncs As $key => $mod)
                        if($this->isLaminasModule($mod))
                            if (!in_array($mod, $currentModules))
                                $modules[] = $mod;

                /**
                 * Store required melis module to session
                 * in this case this will be easy to unplug module
                 * that activate during setup needs to deactivate after setup
                 */
                $reqModSessTemp = new Container('melismarketplace');
                $reqModSessTemp['temp_mod_actvt'] = $modules;

                $this->getMarketPlaceService()->plugModule($modules);

                $message = $this->getTool()->getTranslation('tr_melis_market_place_plug_module_ok', [$module]);
                $success = true;
            }
        }

        return new JsonModel([
            'success' => $success,
            'message' => $message
        ]);
    }

    /**
     * @return \Laminas\View\Model\JsonModel
     */
    public function isModuleActiveAction()
    {
        $active = false;

        if ($this->getRequest()->isPost()) {
            /** @var  Laminas\Http\Request $request */
            $request = $this->getRequest();
            $module = $this->getTool()->sanitize($request->getPost('module'));
            /** @var \MelisCore\Service\MelisCoreMelisAssetManagerModulesService $mm */
            $mm = $this->getServiceManager()->get('MelisAssetManagerModulesService');
            $active = $mm->isModuleLoaded($module);
        }

        return new JsonModel([
            'active' => $active
        ]);
    }

    /**
     * @return \Laminas\View\Model\JsonModel
     */
    public function unplugModuleAction()
    {
        $success = false;
        $message = $this->getTool()->getTranslation('tr_melis_market_place_plug_module_ko', ['']);

        if ($this->getRequest()->isPost()) {
            $module = $this->getRequest()->getPost('module');
            if ($module) {

                //Check if module is laminas module
                if($this->isLaminasModule($module))
                    $modules[] = $module;
                else
                    $modules = [];

                // Deactivating temporary activated modules
                $reqModSessTemp = new Container('melismarketplace');

                if (!empty($reqModSessTemp['temp_mod_actvt'])){
                    $modules = array_merge($reqModSessTemp['temp_mod_actvt'], $modules);
                }

                $this->getMarketPlaceService()->unplugModule($modules);

                $message = $this->getTool()->getTranslation('tr_melis_market_place_plug_module_ok', [$module]);
                $success = true;
            }
        }

        return new JsonModel([
            'success' => $success
        ]);
    }

    /**
     * @return \Laminas\View\Model\JsonModel
     * @throws \ReflectionException
     */
    public function getSetupModuleFormAction()
    {
        $module = $this->getRequest()->getPost('module', $this->params()->fromRoute('module'));
        $action = $this->getRequest()->getPost('action', $this->params()->fromRoute('module',static::ACTION_DOWNLOAD)) === self::ACTION_REQUIRE
            ? self::ACTION_DOWNLOAD : self::ACTION_DOWNLOAD;

        $form = null;
        $moduleSite = $this->getServiceManager()->get('MelisAssetManagerModulesService')->isSiteModule($module);

        if ($this->getMarketPlaceService()->hasPostSetup($module, $action)) {
            $form = $this->getMarketPlaceService()->getForm($module);
        }

        return new JsonModel(get_defined_vars());
    }

    /**
     * @return \Laminas\View\Model\JsonModel|null
     * @throws \ReflectionException
     */
    public function validateSetupFormAction()
    {
        $module = $this->getRequest()->getPost('module', $this->params()->fromRoute('module'));
        $action = $this->getRequest()->getPost('action', $this->params()->fromRoute('module',static::ACTION_DOWNLOAD)) === self::ACTION_REQUIRE
            ? self::ACTION_DOWNLOAD : self::ACTION_DOWNLOAD;

        $result = null;
        $post = $this->getTool()->sanitizeRecursive($this->getRequest()->getPost());

        if ($this->getRequest()->getMethod() === 'POST') {
            if ($this->getMarketPlaceService()->hasPostSetup($module, $action)) {
                $result = $this->getMarketPlaceService()->validateForm($module, $post);

            }
        }

        $response = get_defined_vars();
        unset($response['post']);

        return new JsonModel($response);
    }

    /**
     * This will be used to finalize the POST data
     *
     * @return \Laminas\View\Model\JsonModel|null
     * @throws \ReflectionException
     */
    public function submitSetupFormAction()
    {
        $module = $this->getRequest()->getPost('module', $this->params()->fromRoute('module'));
        $action = $this->getRequest()->getPost('action', $this->params()->fromRoute('module',static::ACTION_DOWNLOAD)) === self::ACTION_REQUIRE
            ? self::ACTION_DOWNLOAD : self::ACTION_DOWNLOAD;

        $result = null;
        $post = $this->getTool()->sanitizeRecursive($this->getRequest()->getPost());
        $moduleSite = false;

        if ($this->getRequest()->getMethod() != 'POST') {
            return new JsonModel(get_defined_vars());
        }

        if ($this->getMarketPlaceService()->hasPostSetup($module, $action)) {
            $result = $this->getMarketPlaceService()->submitForm($module, $post);

            /** @var \MelisCore\Service\MelisCoreMelisAssetManagerModulesService $moduleService */
            $moduleService = $this->getServiceManager()->get('MelisAssetManagerModulesService');

            if ($moduleSite = $moduleService->isSiteModule($module)) {
                /** @var \MelisMarketPlace\Service\MelisMarketPlaceSiteService $service */
                $service = $this->getServiceManager()->get('MelisMarketPlaceSiteService');
                try {
                    $service->marketplaceInstallSite($this->getRequest())->invokeSetup();
                    if ($result['success'] === true) {
                        $result['message'] = $this->getTool()->getTranslation('tr_melis_marketplace_setup_config_ok');
                    }
                } catch (\Exception $e) {
                    $result['message'] = $e->getMessage();
                }

            }
        }

        return new JsonModel(array_merge($result, ['moduleSite' => $moduleSite]));
    }

    public function siteInstallAction()
    {
        $module = $this->getRequest()->getPost('module', 'MelisDemoCms');
        $action = $this->getRequest()->getPost('action', 'download');

        $start = microtime(true);
        /** @var \MelisMarketPlace\Service\MelisMarketPlaceSiteService $service */
        $service = $this->getServiceManager()->get('MelisMarketPlaceSiteService');
        $test = $service->installSite($this->getRequest())->invokeSetup();
        $timeElapsed = microtime(true) - $start;
        $test = $service->installSite($this->getRequest())->invokeSetup();

        /** @var \MelisCore\Service\MelisCoreMelisAssetManagerModulesService $moduleService */
//        $moduleService = $this->getServiceManager()->get('MelisAssetManagerModulesService');
//
//        $data = $moduleService->isSiteModule($module);
//        dd($data);

        dd("$timeElapsed sec");
    }
}
