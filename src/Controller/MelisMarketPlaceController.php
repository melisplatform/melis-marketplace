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

        $response = file_get_contents($url.'/get-most-downloaded-packages');
        $packages = Json::decode($response, Json::TYPE_ARRAY);

        $view = new ViewModel();

        $view->melisKey             = $melisKey;
        $view->melisPackagistServer = $this->getMelisPackagistServer();
        $view->packages             = $packages;
        $view->setVariable('searchForm', $searchForm);

        return $view;
    }

    public function testerAction()
    {
        echo 'test';
        $data = file_get_contents('http://marketplace.melisplatform.com/melis-packagist/get-packages/page/1/search//item_per_page/8/order/asc/order_by/mp_title');
        $data = (array) json_decode($data);
        print '<pre>';
        print_r($data);
        print '</pre>';
        die;
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


        $response = file_get_contents($url.'/get-package/'.$packageId);
        $package  = (array) json_decode($response);

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

            $post = get_object_vars($this->getRequest()->getPost());

            $page        = isset($post['page'])        ? (int) $post['page']        : 1;
            $search      = isset($post['search'])      ? $post['search']            :  '';
            $orderBy     = isset($post['orderBy'])     ? $post['orderBy']           : 'mp_title';
            $order       = isset($post['order'])       ? $post['order']             : 'asc';
            $itemPerPage = isset($post['itemPerPage']) ? (int) $post['itemPerPage'] : 8;

            $serverPackages = file_get_contents($this->getMelisPackagistServer().'/get-packages/page/'.$page.'/search/'.$search
                .'/item_per_page/'.$itemPerPage.'/order/'.$order.'/order_by/'.$orderBy);

            $serverPackages    = Json::decode($serverPackages, Json::TYPE_ARRAY);
            $packages          = isset($serverPackages['packages'])          ? $serverPackages['packages']          : null;
            $itemCountPerPage  = isset($serverPackages['itemCountPerPage'])  ? $serverPackages['itemCountPerPage']  : null;
            $pageCount         = isset($serverPackages['pageCount'])         ? $serverPackages['pageCount']         : null;
            $currentPageNumber = isset($serverPackages['currentPageNumber']) ? $serverPackages['currentPageNumber'] : null;
            $pagination        = isset($serverPackages['pagination'])        ? $serverPackages['pagination']        : null;

        }

        return new ViewModel(array(
            'packages'          => $packages,
            'itemCountPerPage'  => $itemCountPerPage,
            'pageCount'         => $pageCount,
            'currentPageNumber' => $currentPageNumber,
            'pagination'        => $pagination
        ));

    }

    public function getMostPackageDownloadsAction()
    {
        $packages = array();

        if($this->getRequest()->isPost()) {

            $post     = get_object_vars($this->getRequest()->getPost());
            $packages = isset($post['packages']) ? $post['packages'] : null;

        }

        return new ViewModel(array(
            'packages' => $packages,
        ));
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
        $config = $this->getServiceLocator()->get('MelisCoreConfig');
        $server = $config->getItem('melis_market_place_tool_config/datas/')['melis_packagist_server'];

        if($server)
            return $server;
    }
}