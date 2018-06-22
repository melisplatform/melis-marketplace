<?php
/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisMarketPlace\Controller\DashboardPlugins;

use MelisCore\Controller\DashboardPlugins\MelisCoreDashboardTemplatingPlugin;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Session\Container;

class MelisMarketPlaceDashboardPluginMostDownloadedPackages extends MelisCoreDashboardTemplatingPlugin
{
    public function __construct()
    {
        //set plugin. first index of plugin in dashboard plugin config
        $this->pluginModule = 'melismarketplace';
        parent::__construct();
    }

    public function mostDownloadedPackages()
    {
        $url                   = $this->getMelisPackagistServer() . "/get-most-downloaded-packages";
        $moduleService         = $this->getServiceLocator()->get('ModulesService');
        $data                  = array();
        $downloadedmodulesData = array();
        $packages              = array();

        //set to no time limit
        set_time_limit(0);
        //set memory limit on ini config
        ini_set('memory_limit', '-1');
        //reads file into string
        $downloadedmodulesData = @file_get_contents($url);

        try{
            $packages   = json_decode($downloadedmodulesData, true);
        }catch (\Exception $e){
            $packages = null;
        }

        //get all modules
        $moduleList = $moduleService->getAllModules();

        if(isset($packages['packages']))
            foreach ($packages['packages'] as $packagesData => $packagesValue)
            {
                $data[] = array(
                    'packageId'              => $packagesValue['packageId'],
                    'packageTitle'           => $packagesValue['packageTitle'],
                    'packageName'            => $packagesValue['packageName'],
                    'packageSubtitle'        => $packagesValue['packageSubtitle'],
                    'packageModuleName'      => $packagesValue['packageModuleName'],
                    'packageDescription'     => $packagesValue['packageDescription'],
                    'packageImages'          => isset($packagesValue['packageImages'][0]) ? $packagesValue['packageImages'][0] : null ,
                    'packageUrl'             => $packagesValue['packageUrl'],
                    'packageRepository'      => $packagesValue['packageRepository'],
                    'packageTotalDownloads'  => $packagesValue['packageTotalDownloads'],
                    'packageVersion'         => $packagesValue['packageVersion'],
                    'packageTimeOfRelease'   => $packagesValue['packageTimeOfRelease'],
                    'packageMaintainers'     => $packagesValue['packageMaintainers'],
                    'packageType'            => $packagesValue['packageType'],
                    'packageDateAdded'       => $packagesValue['packageDateAdded'],
                    'packageLastUpdate'      => $packagesValue['packageLastUpdate'],
                    'packageGroupId'         => $packagesValue['packageGroupId'],
                    'packageGroupName'       => $packagesValue['packageGroupName'],
                    'packageIsActive'        => $packagesValue['packageIsActive'],

                );
            }


        $view = new ViewModel();

        $view->downloadedPackages = $data;
        $view->setTemplate('MelisMarketplaceDashboardPluginMostDownloadedPackages/most-downloaded-packages');

        return $view;
    }

    /*
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
}