<?php

namespace MelisMarketPlace\Controller;

use Illuminate\View\View;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Json\Json;
use PDO;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\Sql\Ddl;

/**
 * Class MelisMarketPlaceController
 * @package MelisMarketPlace\Controller
 */
class MelisMarketPlaceController extends AbstractActionController
{
    /** @var  \Zend\Db\Adapter\Adapter $adapter */
    protected $adapter;

    const ACTION_REQUIRE = 'require';
    const ACTION_DOWNLOAD = 'download';

    /**
     * Handles the display of the tool
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function toolContainerAction()
    {
        $url = $this->getMelisPackagistServer();
        $melisKey = $this->getMelisKey();
        $config = $this->getServiceLocator()->get('MelisCoreConfig');
        $searchForm = $config->getItem('melis_market_place_tool_config/forms/melis_market_place_search');

        $packageGroupData = @file_get_contents($url . '/get-package-group', true);

        try {
            $packageGroupData = Json::decode($packageGroupData, Json::TYPE_ARRAY);
        } catch (\Exception $e) {
            $packageGroupData = null;
        }

        $factory = new \Zend\Form\Factory();
        $formElements = $this->getServiceLocator()->get('FormElementManager');
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
        $config = $this->getServiceLocator()->get('MelisCoreConfig');
        $server = $config->getItem('melis_market_place_tool_config/datas/')['melis_packagist_server'];

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
        $platformTable = $this->getServiceLocator()->get('MelisCoreTablePlatform');
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
     * @return \Zend\View\Model\ViewModel
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
            $marketPlaceService = $this->getServiceLocator()->get('MelisMarketPlaceService');
            $moduleSvc = $this->getServiceLocator()->get('ModulesService');

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
        $platformTbl = $this->getServiceLocator()->get('MelisCoreTablePlatform');
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
        $service = $this->getServiceLocator()->get('MelisMarketPlaceService');
        return $service;
    }

    /**
     * @return \MelisCore\Service\MelisCoreToolService
     */
    private function getTool()
    {
        /** @var \MelisCore\Service\MelisCoreToolService $tool */
        $tool = $this->getServiceLocator()->get('MelisCoreTool');
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
        $config = $this->getServiceLocator()->get('MelisCoreConfig');
        $modules = $config->getItem('melis_market_place_tool_config/datas/')['exceptions'];

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
        if ($this->getServiceLocator()->get('ModulesService')->getModulePath($module)) {
            return true;
        }

        return false;
    }

    /**
     * Translates the retrieved data coming from the Melis Packagist URL
     * and transform's it into a display including the pagination
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function packageListAction()
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
            $config = $this->getServiceLocator()->get('MelisCoreConfig');
            $searchForm = $config->getItem('melis_market_place_tool_config/forms/melis_market_place_search');


            //end verifying modules

            $factory = new \Zend\Form\Factory();
            $formElements = $this->getServiceLocator()->get('FormElementManager');
            $factory->setFormElementManager($formElements);
            $searchForm = $factory->createForm($searchForm);

            $post = $this->getTool()->sanitizeRecursive(get_object_vars($this->getRequest()->getPost()), [], true);

            $page = isset($post['page']) ? (int) $post['page'] : 1;
            $search = isset($post['search']) ? $post['search'] : '';
            $orderBy = isset($post['orderBy']) ? $post['orderBy'] : 'mp_total_downloads';
            $order = isset($post['order']) ? $post['order'] : 'desc';
            $itemPerPage = isset($post['itemPerPage']) ? (int) $post['itemPerPage'] : 8;
            $group = isset($this->getRequest()->getQuery()['group']) ? (string) $this->getRequest()->getQuery()['group'] : null;


            set_time_limit(0);
            ini_set('memory_limit', '-1');
            $search = urlencode($search);
            $requestJsonUrl = $this->getMelisPackagistServer() . '/get-packages/page/' . $page . '/search/' . $search
                . '/item_per_page/' . $itemPerPage . '/order/' . $order . '/order_by/' . $orderBy . '/status/1' . '/group/' . $group;

            $serverPackages = @file_get_contents($requestJsonUrl);
            try {
                $serverPackages = Json::decode($serverPackages, Json::TYPE_ARRAY);
            } catch (\Exception $e) {
                $serverPackages = null;
            }
            $tmpPackages = empty($serverPackages['packages']) ?: $serverPackages['packages'];


            if (isset($serverPackages['packages']) && $serverPackages['packages']) {
                // check if the module is installed
                $installedModules = $this->getServiceLocator()->get('ModulesService')->getAllModules();
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
     * @return \Zend\View\Model\ViewModel
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
     * @return \Zend\View\Model\ViewModel
     */
    public function toolProductModalContentAction()
    {
        $module = $this->getTool()->sanitize($this->params()->fromQuery('module', ''));
        $action = $this->getTool()->sanitize($this->params()->fromQuery('action', ''));

        $melisKey = $this->params()->fromRoute('melisKey', '');
        $title = $this->getTool()->getTranslation('tr_market_place_' . $action) . ' ' . $module;
        $data = [];
        $status = '';
        $composerSvc = $this->getServiceLocator()->get('MelisComposerService');

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
     * @return \Zend\View\Model\ViewModel
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
     * @return \Zend\View\Model\ViewModel
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

            $moduleSvc = $this->getServiceLocator()->get('ModulesService');
            $post = $this->getTool()->sanitizeRecursive($request->getPost()->toArray());

            $this->getEventManager()->trigger('melis_marketplace_product_do_start', $this, $post);

            $action = isset($post['action']) ? $post['action'] : '';
            $package = isset($post['package']) ? $post['package'] : '';
            $module = isset($post['module']) ? $post['module'] : '';

            if ($action && $package && $module) {

                $title = $this->getTool()->getTranslation('tr_market_place_' . $action) . ' ' . $module;
                $composerSvc = $this->getServiceLocator()->get('MelisComposerService');

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

                            // read the composer.json file
                            $composerJsonFile = $_SERVER['DOCUMENT_ROOT'] . '/../composer.json';

                            if (file_exists($composerJsonFile)) {
                                // read the composer.json file
                                set_time_limit(0);
                                ini_set('memory_limit', '-1');
                                try {
                                    $composerJson = json_decode(@file_get_contents($composerJsonFile), true);
                                } catch (\Exception $e) {
                                    $composerJson = null;
                                }

                                $modulePath = $moduleSvc->getModulePath($module);
                                if (file_exists($modulePath)) {

                                    if (file_exists("$modulePath/.gitignore")) {
                                        unlink("$modulePath/.gitignore");
                                    }

                                    if ($this->hasDirRights($modulePath)) {
                                        $this->deleteDir("$modulePath/.git");
                                        $this->deleteDir($modulePath);
                                    }
                                }

                                // update the content of composer.json
                                $require = isset($composerJson['require']) ? $composerJson['require'] : null;
                                if ($require) {
                                    unset($require[$package]);
                                    $composerJson['require'] = $require;
                                }

                                $newContent = \Zend\Json\Json::encode($composerJson, false, ['prettyPrint' => true]);
                                $newContent = str_replace('\/', '/', $newContent);

                                unlink($composerJsonFile);
                                file_put_contents($composerJsonFile, $newContent);
                            }

                            $defaultModules = ['MelisAssetManager', 'MelisComposerDeploy', 'MelisDbDeploy', 'MelisCore'];
                            $removeModules = array_merge($moduleSvc->getChildDependencies($module), [$module, 'MelisModuleConfig']);
                            $activeModules = $moduleSvc->getActiveModules($defaultModules);

                            // create new module.load file
                            $retainModules = [];

                            foreach ($activeModules as $module) {
                                if (!in_array($module, $removeModules)) {
                                    $retainModules[] = $module;
                                }
                            }
                            $moduleSvc->createModuleLoader('config/', $retainModules, $defaultModules);
                            $composerSvc->dumpAutoload();
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
                    if (filetype($dirPath . "/" . $object) == "dir") {
                        $this->deleteDir($dirPath . "/" . $object);
                    } else {
                        chmod($dirPath . "/" . $object, 0777);
                        unlink($dirPath . "/" . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dirPath);
        }
    }

    /**
     * @return \Zend\View\Model\JsonModel
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
            $moduleSvc = $this->getServiceLocator()->get('ModulesService');
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
     * @return \Zend\View\Model\JsonModel
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
            $moduleSvc = $this->getServiceLocator()->get('ModulesService');
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
     * @return \Zend\View\Model\JsonModel
     */
    public function activateModuleAction()
    {
        $success = 0;
        $request = $this->getRequest();

        if ($request->isPost()) {
            $module = $this->getTool()->sanitize($request->getPost('module'));
            $moduleSvc = $this->getServiceLocator()->get('ModulesService');
            $activeModules = $moduleSvc->getActiveModules();

            if (!in_array($module, $activeModules)) {

                // since we are still running the function, we cannot get the accurate modules that are being loaded
                // instead, we can read the module.load
                $moduleLoadFile = $_SERVER['DOCUMENT_ROOT'] . '/../config/melis.module.load.php';

                if (file_exists($moduleLoadFile)) {

                    $modules = include $_SERVER['DOCUMENT_ROOT'] . '/../config/melis.module.load.php';

                    $moduleCount = count($modules);
                    $insertAtIdx = $moduleCount - 1;
                    array_splice($modules, $insertAtIdx, 0, $module);

                    // create the module.load file
                    $moduleSvc->createModuleLoader('config/', $modules, [], []);

                    // recheck if the module requested to be added is in module.load
                    if (in_array($module, $modules)) {
                        $success = 1;
                    }
                }
            }
        }

        $response = [
            'success' => $success,
        ];

        return new JsonModel($response);
    }

    /**
     * @return \Zend\View\Model\JsonModel
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
     * @return \Zend\View\Model\JsonModel
     */
    public function getModuleTablesAction()
    {
        $module = $this->getTool()->sanitize($this->getRequest()->getPost('module'));
        $tables = [];
        $files = [];

        if ($this->getRequest()->isPost()) {
            $svc = $this->getServiceLocator()->get('ModulesService');
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
     * @return \Zend\View\Model\JsonModel|\Zend\View\Model\ViewModel
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
     * @return \Zend\Db\Adapter\Adapter
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
        $config = $this->getServiceLocator()->get('config');
        $db = $config['db'];

        if ($db) {

            $driver = $db['driver'];
            $dsn = $db['dsn'];
            $username = $db['username'];
            $password = $db['password'];

            $this->adapter = new DbAdapter([
                'driver' => $driver,
                'dsn' => $dsn,
                'username' => $username,
                'password' => $password,
                'driver_options' => [
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
                ],
            ]);
        }
    }

    /**
     * @return \Zend\View\Model\JsonModel
     */
    public function execDbDeployAction()
    {
        $success = false;
        $request = $this->getRequest();

        if ($request->isPost()) {
            $module = $this->getTool()->sanitize($request->getPost('module'));
            if ($module) {
                /** @var \MelisDbDeploy\Service\MelisDbDeployDiscoveryService $deployDiscoveryService */
                $deployDiscoveryService = $this->getServiceLocator()->get('MelisDbDeployDiscoveryService');
                $deployDiscoveryService->processing($module);
                $success = true;
            }
        }

        return new JsonModel([
            'success' => $success,
        ]);
    }

    /**
     * dashboard view of market place
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function marketPlaceDashboardAction()
    {
        $url = $this->getMelisPackagistServer() . "/get-most-downloaded-packages";
        $melisKey = $this->getmelisKey();
        $moduleService = $this->getServiceLocator()->get('ModulesService');
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

    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function marketPlaceModuleHeaderAction()
    {
        $melisKey = $this->getMelisKey();

        $moduleService = $this->getServiceLocator()->get('ModulesService');
        $marketplaceService = $this->getServiceLocator()->get('MelisMarketPlaceService');
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
     * @return \Zend\View\Model\JsonModel
     */
    public function plugModuleAction()
    {
        $success = false;
        $message = $this->getTool()->getTranslation('tr_melis_market_place_plug_module_ko', ['']);

        if ($this->getRequest()->isPost()) {
            $module = $this->getTool()->sanitizeRecursive($this->getRequest()->getPost());

            if ($module && isset($module['module'])) {
                $module = $module['module'];
                $this->getMarketPlaceService()->plugModule($module);
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
     * @return \Zend\View\Model\JsonModel
     */
    public function isModuleActiveAction()
    {
        $active = false;

        if ($this->getRequest()->isPost()) {
            /** @var  Zend\Http\Request $request */
            $request = $this->getRequest();
            $module = $this->getTool()->sanitize($request->getPost('module'));
            /** @var \MelisCore\Service\MelisCoreModulesService $mm */
            $mm = $this->getServiceLocator()->get('ModulesService');
            $active = $mm->isModuleLoaded($module);
        }

        return new JsonModel([
            'active' => $active
        ]);
    }

    /**
     * @return \Zend\View\Model\JsonModel
     */
    public function unplugModuleAction()
    {
        $success = false;
        $message = $this->getTool()->getTranslation('tr_melis_market_place_plug_module_ko', ['']);

        if ($this->getRequest()->isPost()) {
            $module = $this->getRequest()->getPost('module');
            if ($module) {
                $this->getMarketPlaceService()->unplugModule($module);
                $message = $this->getTool()->getTranslation('tr_melis_market_place_plug_module_ok', [$module]);
                $success = true;
            }
        }

        return new JsonModel([
            'success' => $success
        ]);
    }

    /**
     * @return \Zend\View\Model\JsonModel
     * @throws \ReflectionException
     */
    public function getSetupModuleFormAction()
    {
        $module = $this->getRequest()->getPost('module', 'MelisDemoCms');
        $action = $this->getRequest()->getPost('action', 'download') === self::ACTION_REQUIRE ? self::ACTION_DOWNLOAD : self::ACTION_DOWNLOAD;
        $form = null;

        if ($this->getMarketPlaceService()->hasPostSetup($module, $action)) {
            $form = $this->getMarketPlaceService()->getForm($module);
        }

        return new JsonModel(get_defined_vars());
    }

    /**
     * @return \Zend\View\Model\JsonModel|null
     * @throws \ReflectionException
     */
    public function validateSetupFormAction()
    {
        $module = $this->getRequest()->getPost('module', 'MelisCore');
        $action = $this->getRequest()->getPost('action', 'download') === self::ACTION_REQUIRE ? self::ACTION_DOWNLOAD : self::ACTION_DOWNLOAD;
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
     * @return \Zend\View\Model\JsonModel|null
     * @throws \ReflectionException
     */
    public function submitSetupFormAction()
    {
        $module = $this->getRequest()->getPost('module', 'MelisCore');
        $action = $this->getRequest()->getPost('action', 'download') === self::ACTION_REQUIRE ? self::ACTION_DOWNLOAD : self::ACTION_DOWNLOAD;
        $result = null;
        $post = $this->getTool()->sanitizeRecursive($this->getRequest()->getPost());

        if ($this->getRequest()->getMethod() != 'POST') {
            return new JsonModel(get_defined_vars());
        }

        if ($this->getMarketPlaceService()->hasPostSetup($module, $action)) {
            $result = $this->getMarketPlaceService()->submitForm($module, $post);

            /** @var \MelisCore\Service\MelisCoreModulesService $moduleService */
            $moduleService = $this->getServiceLocator()->get('ModulesService');

            if ($moduleService->isSiteModule($module)) {
                $service = $this->getServiceLocator()->get('MelisMarketPlaceSiteService');
                $test = $service->installSite($this->getRequest())->invokeSetup();
            }
        }

        return new JsonModel($result);
    }

    public function siteInstallAction()
    {
        $module = $this->getRequest()->getPost('module', 'MelisDemoCms');
        $action = $this->getRequest()->getPost('action', 'download');

        $start = microtime(true);
        /** @var \MelisMarketPlace\Service\MelisMarketPlaceSiteService $service */
        $service = $this->getServiceLocator()->get('MelisMarketPlaceSiteService');
        $test = $service->installSite($this->getRequest())->invokeSetup();
        $timeElapsed = microtime(true) - $start;
//        $test = $service->installSite($this->getRequest());

        /** @var \MelisCore\Service\MelisCoreModulesService $moduleService */
//        $moduleService = $this->getServiceLocator()->get('ModulesService');
//
//        $data = $moduleService->isSiteModule($module);
//        dd($data);

        dd("$timeElapsed sec");
    }
}
