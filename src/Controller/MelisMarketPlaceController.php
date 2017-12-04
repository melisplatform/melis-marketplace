<?php
namespace MelisMarketPlace\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Json\Json;
/**
 * Class MelisMarketPlaceController
 * @package MelisMarketPlace\Controller
 */
class MelisMarketPlaceController extends AbstractActionController
{

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
//echo $version.PHP_EOL;
//echo $this->getVersionStatusText($version);
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

        $view             = new ViewModel();
        $view->melisKey   = $melisKey;
        $view->packageId  = $packageId;
        $view->package    = $package;
        $view->packages   = $packages;
        $view->isModuleInstalled    = $isModuleInstalled;
        $view->melisPackagistServer = $url;
        $view->isExempted = $isExempted;

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


        return $view;
    }

    public function toolProductModalContentAction()
    {
        $package  = $this->getTool()->sanitize($this->params()->fromQuery('module', ''));
        $action   = $this->getTool()->sanitize($this->params()->fromQuery('action', ''));

        $melisKey = $this->params()->fromRoute('melisKey', '');
        $title    = $this->getTool()->getTranslation('tr_market_place_'.$action) . ' ' .  $package;
        $data     = array();

        $view = new ViewModel();

        $view->melisKey = $melisKey;
        $view->title    = $title;


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
                        if(!in_array($module, $this->getModuleExceptions()))
                            $composerSvc->download($package);
                    break;
                    case $composerSvc::UPDATE:
                        $composerSvc->update($package);
                    break;
                    case $composerSvc::REMOVE:
                        if(!in_array($module, $this->getModuleExceptions())) {

                            $defaultModules = array('MelisAssetManager','MelisCore', 'MelisEngine', 'MelisFront');
                            $removeModules  = array_merge($moduleSvc->getChildDependencies($module), array($module, 'MelisModuleConfig'));
                            $activeModules  = $moduleSvc->getActiveModules($defaultModules);

                            // create new module.load file
                            $retainModules = array();

                            foreach($activeModules as $module) {
                                if(!in_array($module, $removeModules)) {
                                    $retainModules[] = $module;
                                }
                            }

                            $moduleSvc->createModuleLoader('config/', $retainModules, $defaultModules);

                            // remove module
//                            $composerSvc->remove($package);

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
die;
        return $view;

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

    public function testAction()
    {
        $svc = $this->getServiceLocator()->get('MelisMarketPlaceComposerService');
        $svc->update('melisplatform/melis-cms');
        exit;
    }

}