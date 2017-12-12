<?php
namespace MelisMarketPlace\Controller;

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

    /**
     * @var DbAdapter
     */
    protected $adapter;

    /**
     * Handles the display of the tool
     * @return ViewModel
     */
    public function toolContainerAction()
    {
        $url       = $this->getMelisPackagistServer();
        $melisKey   = $this->getMelisKey();
        $config     = $this->getServiceLocator()->get('MelisCoreConfig');
        $searchForm = $config->getItem('melis_market_place_tool_config/forms/melis_market_place_search');

        $factory      = new \Zend\Form\Factory();
        $formElements = $this->serviceLocator->get('FormElementManager');
        $factory->setFormElementManager($formElements);
        $searchForm   = $factory->createForm($searchForm);

        set_time_limit(0);
        $response = file_get_contents($url.'/get-most-downloaded-packages');
        $packages = Json::decode($response, Json::TYPE_ARRAY);

        $view = new ViewModel();

        $view->melisKey             = $melisKey;
        $view->melisPackagistServer = $url;
        $view->packages             = $packages;
        $view->setVariable('searchForm', $searchForm);

        return $view;
    }

    /**
     * Handles the display of a specific package
     * @return ViewModel
     */
    public function toolContainerProductViewAction()
    {
        $url       = $this->getMelisPackagistServer();
        $melisKey  = $this->getMelisKey();
        $packageId = (int) $this->params()->fromQuery('packageId', null);

        set_time_limit(0);
        $response   = file_get_contents($url.'/get-package/'.$packageId);
        $package    = Json::decode($response, Json::TYPE_ARRAY);
        $isExempted = false;

        //get and compare the local version from repo
        if(!empty($package)){

            //get marketplace service
            $marketPlaceService = $this->getServiceLocator()->get('MelisMarketPlaceService');

            //compare the package local version to the repository
            if(isset($package['packageModuleName'])) {

                $version = $marketPlaceService->compareLocalVersionFromRepo($package['packageModuleName'], $package['packageVersion']);

                if(!empty($d)){
                    $package['version_status'] = $this->getVersionStatusText($version);
                }else{
                    $package['version_status'] = "";
                }

                if(in_array($package['packageModuleName'],  $this->getModuleExceptions())) {
                    $isExempted = true;
                }
            }
        }

        set_time_limit(0);
        $response = file_get_contents($url.'/get-most-downloaded-packages');
        $packages = Json::decode($response, Json::TYPE_ARRAY);

        $isModuleInstalled = (bool) $this->isModuleInstalled($package['packageModuleName']);

        $view                       = new ViewModel();
        $view->melisKey             = $melisKey;
        $view->packageId            = $packageId;
        $view->package              = $package;
        $view->packages             = $packages;
        $view->isModuleInstalled    = $isModuleInstalled;
        $view->melisPackagistServer = $url;
        $view->isExempted           = $isExempted;
        $view->versionStatus        = $version;
        $view->versionText          = $this->getVersionStatusText($version);

        return $view;
    }



    /**
     * Translates the retrieved data coming from the Melis Packagist URL
     * and transform's it into a display including the pagination
     * @return ViewModel
     */
    public function packageListAction()
    {
        $packages          = array();
        $itemCountPerPage  = 1;
        $pageCount         = 1;
        $currentPageNumber = 1;

        if($this->getRequest()->isPost()) {

            $post = $this->getTool()->sanitizeRecursive(get_object_vars($this->getRequest()->getPost()), array(), true);

            $page        = isset($post['page'])        ? (int) $post['page']        : 1;
            $search      = isset($post['search'])      ? $post['search']            : '';
            $orderBy     = isset($post['orderBy'])     ? $post['orderBy']           : 'mp_total_downloads';
            $order       = isset($post['order'])       ? $post['order']             : 'desc';
            $itemPerPage = isset($post['itemPerPage']) ? (int) $post['itemPerPage'] : 8;

            set_time_limit(0);
            $search         = urlencode($search);
            $requestJsonUrl = $this->getMelisPackagistServer().'/get-packages/page/'.$page.'/search/'.$search
                .'/item_per_page/'.$itemPerPage.'/order/'.$order.'/order_by/'.$orderBy.'/status/1';

            try {
                $serverPackages = file_get_contents($requestJsonUrl);
            }catch(\Exception $e) {}

            $serverPackages = Json::decode($serverPackages, Json::TYPE_ARRAY);
            $tmpPackages    = empty($serverPackages['packages']) ?: $serverPackages['packages'];

            if(isset($serverPackages['packages']) && $serverPackages['packages']) {
                // check if the module is installed
                $installedModules = $this->getServiceLocator()->get('ModulesService')->getAllModules();
                $installedModules = array_map(function($a) {
                    return trim(strtolower($a));
                }, $installedModules);


                // rewrite array, add installed status
                foreach($serverPackages['packages'] as $idx => $package) {

                    // to make sure it will match
                    $packageName = trim(strtolower($package['packageModuleName']));

                    if(in_array($packageName, $installedModules)) {
                        $tmpPackages[$idx]['installed'] = true;
                    }
                    else {
                        $tmpPackages[$idx]['installed'] = false;
                    }

                    //compare the package local version to the repository
                    if(isset($tmpPackages[$idx]['packageModuleName'])) {
                        $d = $this->getMarketPlaceService()->compareLocalVersionFromRepo($tmpPackages[$idx]['packageModuleName'], $tmpPackages[$idx]['packageVersion']);
                        if(!empty($d)){
                            $tmpPackages[$idx]['version_status'] = $d['version_status'];
                        }else{
                            $tmpPackages[$idx]['version_status'] = "";
                        }
                    }
                }

                $serverPackages['packages'] = $tmpPackages;
            }


            $packages          = isset($serverPackages['packages'])          ? $serverPackages['packages']          : null;
            $itemCountPerPage  = isset($serverPackages['itemCountPerPage'])  ? $serverPackages['itemCountPerPage']  : null;
            $pageCount         = isset($serverPackages['pageCount'])         ? $serverPackages['pageCount']         : null;
            $currentPageNumber = isset($serverPackages['currentPageNumber']) ? $serverPackages['currentPageNumber'] : null;
            $pagination        = isset($serverPackages['pagination'])        ? $serverPackages['pagination']        : null;

        }

        $view = new ViewModel();

        $view->setTerminal(true);

        $view->packages          = $packages;
        $view->itemCountPerPage  = $itemCountPerPage;
        $view->pageCount         = $pageCount;
        $view->currentPageNumber = $currentPageNumber;
        $view->pagination        = $pagination;

        return $view;

    }

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

    public function toolProductModalContentAction()
    {
        $module   = $this->getTool()->sanitize($this->params()->fromQuery('module', ''));
        $action   = $this->getTool()->sanitize($this->params()->fromQuery('action', ''));

        $melisKey = $this->params()->fromRoute('melisKey', '');
        $title    = $this->getTool()->getTranslation('tr_market_place_'.$action) . ' ' .  $module;
        $data     = array();

        $view = new ViewModel();

        $view->melisKey = $melisKey;
        $view->title    = $title;
        $view->module   = $module;


        return $view;
    }

    public function melisMarketPlaceProductDoAction()
    {

        $success = 0;
        $message = 'melis_market_place_tool_package_do_event_message_ko';
        $errors  = array();
        $request = $this->getRequest();
        $title   = 'tr_market_place';
        $post    = array();
        
        if($request->isPost()) {

            $moduleSvc = $this->getServiceLocator()->get('ModulesService');
            $post      = $this->getTool()->sanitizeRecursive($request->getPost()->toArray());

            $this->getEventManager()->trigger('melis_marketplace_product_do_start', $this, $post);

            $action  = isset($post['action'])  ? $post['action']  : '';
            $package = isset($post['package']) ? $post['package'] : '';
            $module  = isset($post['module'])  ? $post['module']  : '';

            if($action && $package && $module) {

                $title       = $this->getTool()->getTranslation('tr_market_place_'.$action) . ' ' .  $module;
                $composerSvc = $this->getServiceLocator()->get('MelisMarketPlaceComposerService');

                switch($action) {
                    case $composerSvc::DOWNLOAD:
                        if(!in_array($module, $this->getModuleExceptions())) {
                            $composerSvc->download($package);
                        }
                    break;
                    case $composerSvc::UPDATE:
//                        $composerSvc->update($package);
                    break;
                    case $composerSvc::REMOVE:
                        if(!in_array($module, $this->getModuleExceptions())) {

                            $defaultModules = array('MelisAssetManager','MelisCore', 'MelisEngine', 'MelisFront');
                            $removeModules  = array_merge($moduleSvc->getChildDependencies($module), array($module, 'MelisModuleConfig'));
                            $activeModules  = $moduleSvc->getActiveModules($defaultModules);

                            // create new module.load file
                            $retainModules  = array();

                            foreach($activeModules as $module) {
                                if(!in_array($module, $removeModules)) {
                                    $retainModules[] = $module;
                                }
                            }

                            $moduleSvc->createModuleLoader('config/', $retainModules, $defaultModules);

                            // remove module
                            $composerSvc->remove($package);

                        }
                    break;
                    default:
                        echo $this->getTool()->getTranslation($message);
                    break;
                }
            }
        }

        $response = array(
            'success' => $success,
            'title'   => $this->getTool()->getTranslation($title),
            'message' => $this->getTool()->getTranslation($message),
            'errors'  => $errors,
            'post'    => $post
        );

        // add to flash messenger
        $this->getEventManager()->trigger('melis_marketplace_product_do_finish', $this, $response);

        $view = new ViewModel();
        $view->setTerminal(true);

        return $view;

    }

    public function activateModuleAction()
    {
        $success = 0;
        $request = $this->getRequest();

        if($request->isPost()) {
            $module        = $this->getTool()->sanitize($request->getPost('module'));
            $moduleSvc     = $this->getServiceLocator()->get('ModulesService');
            $activeModules = $moduleSvc->getActiveModules();

            if(!in_array($module, $activeModules)) {
                // add to module loader
                $defaultModules = array('MelisAssetManager','MelisCore', 'MelisEngine', 'MelisFront');

                // remove MelisModuleConfig, to avoid duplication
                $idx = array_search('MelisModuleConfig', $activeModules);
                if (false !== $idx) {
                    unset($activeModules[$idx]);
                }

                // create the module.load file
                $moduleSvc->createModuleLoader('config/', array_merge($activeModules, array($module)), $defaultModules);
            }

            // since we are still running the function, we cannot get the accurate modules that are being loaded
            // instead, we can read the module.load
            $moduleLoadFile = $_SERVER['DOCUMENT_ROOT'].'/../config/melis.module.load.php';
            if(file_exists($moduleLoadFile)) {
                $modules = include $_SERVER['DOCUMENT_ROOT'].'/../config/melis.module.load.php';

                // recheck if the module requested to be added is in module.load
                if(in_array($module, $modules)) {
                    $success = 1;
                }
            }

        }

        $response = array(
            'success' => $success
        );

        return new JsonModel($response);
    }



    /**
     * MelisMarketPlace/src/MelisMarketPlace/Controller/MelisMarketPlaceController.php
     * Returns the melisKey of the view that is being set in app.interface
     * @return mixed
     */
    private function getMelisKey()
    {
        $melisKey = $this->params()->fromRoute('melisKey', $this->params()->fromQuery('melisKey'), null);

        return $melisKey;
    }

    /**
     * MelisCoreTool
     * @return array|object
     */
    private function getTool()
    {
        $tool = $this->getServiceLocator()->get('MelisCoreTool');
        return $tool;
    }


    /**
     * Melis Packagist Server URL
     * @return mixed
     */
    private function getMelisPackagistServer()
    {
        $env    = getenv('MELIS_PLATFORM') ?: 'default';
        $config = $this->getServiceLocator()->get('MelisCoreConfig');
        $server = $config->getItem('melis_market_place_tool_config/datas/')['melis_packagist_server'];

        if($server)
            return $server;
    }

    /**
     * Returns the list of modules that is inside the exceptions array
     * @return mixed
     */
    private function getModuleExceptions()
    {
        $env     = getenv('MELIS_PLATFORM') ?: 'default';
        $config  = $this->getServiceLocator()->get('MelisCoreConfig');
        $modules = $config->getItem('melis_market_place_tool_config/datas/')['exceptions'];

        if($modules)
            return $modules;
    }

    /**
     * Checks if the module is installed or not
     * @param $module
     * @return bool
     */
    private function isModuleInstalled($module)
    {
        $installedModules = $this->getServiceLocator()->get('ModulesService')->getAllModules();
        $installedModules = array_map(function($a) {
            return trim(strtolower($a));
        }, $installedModules);

        if(in_array(strtolower($module), $installedModules)) {
            return true;
        }

        return false;
    }

    private function getMarketPlaceService()
    {
        return $this->getServiceLocator()->get('MelisMarketPlaceService');
    }

    private function getVersionStatusText($status)
    {
        $service = $this->getMarketPlaceService();

        switch($status) {
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

    public function isModuleExistsAction()
    {
        $isExist = 0;
        $module  = '';
        $request = $this->getRequest();

        if($request->isPost()) {

            $module = $this->getTool()->sanitize($request->getPost('module'));
            if($module) {
                $isExist = (bool) $this->isModuleInstalled($module);
            }
        }

        $response = array(
            'module'  => $module,
            'isExist' => $isExist
        );

        return new JsonModel($response);

    }

    public function getModuleTablesAction()
    {
        $module         = $this->getTool()->sanitize($this->getRequest()->getPost('module', 'MelisCore'));
        $svc            = $this->getServiceLocator()->get('ModulesService');
        $path           = $svc->getModulePath($module, true);
        $setupStructure = 'setup_structure.sql';
        $setupFile      = $path.'/install/sql/'.$setupStructure;
        $message        = 'No table(s) found';
        $tables         = array();
        if(file_exists($setupFile)) {
            set_time_limit(-1);
            ini_set ('memory_limit', -1);

            $setupFile = file_get_contents($setupFile);
            if(preg_match_all('/CREATE\sTABLE\sIF\sNOT\sEXISTS\s\`(.*?)+\`/', $setupFile, $matches)) {
                $tables = isset($matches[0]) ? $matches[0] : null;
                $tables = array_map(function($a) {
                    $n = str_replace(array('CREATE TABLE IF NOT EXISTS', '`'), '', $a);
                    $n = trim($n);
                    return  $n;
                }, $tables);
                if(is_array($tables)) {
                    $tables = (array) $tables;
                }
            }
        }

        return new JsonModel(array(
            'module' => $module,
            'tables' => $tables
        ));
    }


    public function exportTablesAction()
    {

        $module  = $this->getTool()->sanitize($this->getRequest()->getPost('module'));
        $tables  = $this->getTool()->sanitize($this->getRequest()->getPost('tables'));
        $success = 0;
        $message = 'No table(s) found';
        if($module) {

            $sql            = '';
            $insert         = "INSERT INTO `%s`(%s) VALUES(%s);".PHP_EOL;
            $dumpInfo       = "\n--\n-- Dumping data for table `%s`\n--\n";
            $copyright      = "-- Melis Platform SQL Dump\n-- https://www.melistechnology.com\n";
            $commit         = "\nCOMMIT;";
            $columns        = "";
            $values         = "";
            $export         = "";

            set_time_limit(-1);
            ini_set ('memory_limit', -1);

            // check again if the tables are not empty
            if(is_array($tables)) {

                // trim the matched texts
                $tables = array_map(function($a) {
                    $n = trim($a);
                    return  $n;
                }, $tables);

                $adapter = $this->getAdapter();

                if($this->getAdapter()) {

                    foreach($tables as $table) {
                        $resultSet = $adapter->query("SELECT * FROM `$table`", DbAdapter::QUERY_MODE_EXECUTE)->toArray();

                        if($resultSet) {
                            // CREATE AN INSERT SQL FILE
                            $sql .= sprintf($dumpInfo, $table);
                            foreach($resultSet as $data) {

                                // clear columns and values every loop
                                $columns   = '';
                                $values    = '';

                                foreach($data as $column => $value) {
                                    $columns .= "`$column`, ";

                                    if(is_numeric($value) || $value == '0')
                                        $values  .= "$value, ";
                                    else if(is_null($value))
                                        $values  .= "NULL, ";
                                    else
                                        $values  .= "'$value', ";
                                }

                                $columns = substr($columns, 0, strlen($columns)-2);
                                $values  = substr($values,  0, strlen($values) -2);
                                $sql    .= sprintf($insert, $table, $columns, $values);
                            }
                        }
                    }
                }
            }

            $export   = $copyright.$sql.$commit;
            $fileName = strtolower($module).'_export_data.sql';
            $response = $this->getResponse();

            $response->getHeaders()
                ->addHeaderLine('Cache-Control'      , 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0')
                ->addHeaderLine('Content-Disposition', 'attachment; filename="'.$fileName)
                ->addHeaderLine('Content-Length'     , strlen($export))
                ->addHeaderLine('Pragma'             , 'no-cache')
                ->addHeaderLine('Content-Type'       , 'application/sql;charset=UTF-8')
                ->addHeaderLine('fileName'           , $fileName);

            $response->setContent($export);
            $response->setStatusCode(200);

            $view = new ViewModel();
            $view->setTerminal(true);

            $view->content = $response->getContent();

            return $view;

        }

        if(!$success) {
            return new JsonModel(array(
                'success' => $success,
                'message' => $message
            ));
        }
    }

    public function execDbDeployAction()
    {
        $moduleSvc = $this->getServiceLocator()->get('ModulesService');
        $composer  = $moduleSvc->getComposer();


        $deployDiscoveryService = $this->getServiceLocator()->get('MelisDbDeployDiscoveryService');
        $test = $deployDiscoveryService->copyDeltas2($composer, 'MelisCmsProspects');
        print_r($test);

//        print_r($composer);
        die;
    }
    public function testAction()
    {
        $class =  __CLASS__;
        $pos   = strrpos($class, 'Controller');
        $end   = strlen($class) - $pos;
        $class = substr($class, 0, strlen($class)-$end);

        $test = $this->forward()->dispatch($class,
            array_merge(array('action' => 'exportTables', 'module' => 'MelisCmsProspects')))->getVariables()->getArrayCopy();


        if(isset($test['content'])) {
            $hhehe = $test['content'];
            echo $hhehe;
            return $test['content'];
        }


        die;
    }

    /**
     * Sets the Database adapter that will be used when querying
     * the database, this will use the configuration set
     * on the database config file
     */
    private function setDbAdapter()
    {
        // access the database configuration
        $config = $this->getServiceLocator()->get('config');
        $db     = $config['db'];

        if($db) {

            $driver = $db['driver'];
            $dsn = $db['dsn'];
            $username = $db['username'];
            $password = $db['password'];

            $this->adapter = new DbAdapter(array(
                'driver' => $driver,
                'dsn' => $dsn,
                'username' => $username,
                'password' => $password,
                'driver_options' => array(
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"
                )
            ));
        }
    }

    /**
     * Returns the instance of DbAdapter
     * @return DbAdapter
     */
    private function getAdapter()
    {
        $this->setDbAdapter();

        return $this->adapter;
    }

}