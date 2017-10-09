<?php
namespace MelisMarketPlace\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
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
        $melisKey   = $this->getMelisKey();
        $config     = $this->getServiceLocator()->get('MelisCoreConfig');
        $searchForm = $config->getItem('melis_market_place_tool_config/forms/melis_market_place_search');

        $factory      = new \Zend\Form\Factory();
        $formElements = $this->serviceLocator->get('FormElementManager');
        $factory->setFormElementManager($formElements);
        $searchForm   = $factory->createForm($searchForm);

        $view = new ViewModel();

        $view->melisKey             = $melisKey;
        $view->melisPackagistServer = $this->getMelisPackagistServer();
        $view->setVariable('searchForm', $searchForm);

        return $view;
    }

    /**
     * Handles the display of a specific package
     * @return ViewModel
     */
    public function toolContainerProductViewAction()
    {
        $server    = $this->getMelisPackagistServer();
        $melisKey  = $this->getMelisKey();
        $packageId = (int) $this->params()->fromQuery('packageId', null);


        $response = file_get_contents($server.'/get-package/'.$packageId);
        $package  = (array) json_decode($response);

        $view            = new ViewModel();
        $view->melisKey  = $melisKey;
        $view->packageId = $packageId;
        $view->package   = $package;
        $view->melisPackagistServer = $server;

        return $view;
    }

    /**
     * Translates the retrieved data coming from the Melis Packagist URL
     * and transform's it into a display including the pagination
     * @return ViewModel
     */
    public function packageListAction()
    {
        $packages = array();
        $itemCountPerPage = 1;
        $pageCount = 1;
        $currentPageNumber = 1;
        if($this->getRequest()->isPost()) {

            $post = get_object_vars($this->getRequest()->getPost());

            $packages          = isset($post['packages'])          ? $post['packages']          : null;
            $itemCountPerPage  = isset($post['itemCountPerPage'])  ? $post['itemCountPerPage']  : null;
            $pageCount         = isset($post['pageCount'])         ? $post['pageCount']         : null;
            $currentPageNumber = isset($post['currentPageNumber']) ? $post['currentPageNumber'] : null;
            $pagination        = isset($post['pagination'])        ? $post['pagination']        : null;


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