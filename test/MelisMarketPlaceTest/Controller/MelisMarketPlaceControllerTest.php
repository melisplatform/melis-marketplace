<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisMarketPlaceTest\Controller;

use MelisCore\ServiceManagerGrabber;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
class MelisMarketPlaceControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = false;
    protected $sm;
    protected $method = 'save';

    public function setUp()
    {
        $this->sm  = new ServiceManagerGrabber();
    }

    private function getMelisPackagistServer()
    {
        $env    = getenv('MELIS_PLATFORM') ?: 'default';
        $config = $this->sm->getServiceManager()->get('MelisCoreConfig');
        $server = $config->getItem('melis_market_place_tool_config/datas/')['melis_packagist_server'];

        if($server)
            return $server;
    }

    

    public function getPayload($method)
    {
        return $this->sm->getPhpUnitTool()->getPayload('MelisMarketPlace', $method);
    }

    /**
     * START ADDING YOUR TESTS HERE
     */

    /**
     * This test will check if the queried URL returns
     * the packages requested.
     */
    public function testGetPackages()
    {
        $requestJsonUrl = $this->getMelisPackagistServer().'/get-packages/page/1/search/'
            .'/item_per_page/1/order/asc/order_by/mp_title/status/1';
        $serverPackages = file_get_contents($requestJsonUrl);
        $data = json_decode($serverPackages, true);

        $this->assertNotEmpty($data);
    }



}

