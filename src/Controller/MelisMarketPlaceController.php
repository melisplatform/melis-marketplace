<?php
namespace MelisMarketPlace\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
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
        $response = file_get_contents($url.'/get-package/'.$packageId);
        $package  = Json::decode($response, Json::TYPE_ARRAY);

        set_time_limit(0);
        $response = file_get_contents($url.'/get-most-downloaded-packages');
        $packages = Json::decode($response, Json::TYPE_ARRAY);

        $view            = new ViewModel();
        $view->melisKey  = $melisKey;
        $view->packageId = $packageId;
        $view->package   = $package;
        $view->packages  = $packages;
        $view->melisPackagistServer = $url;

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
    private function removeMelisPackagistServer()
    {
        $env    = getenv('MELIS_PLATFORM') ?: 'default';


        if($env)
            return $env;
    }
}