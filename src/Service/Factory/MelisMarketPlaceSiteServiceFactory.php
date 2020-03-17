<?php
/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2017 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisMarketPlace\Service\Factory;


use MelisMarketPlace\Service\MelisMarketPlaceSiteService;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class MelisMarketPlaceSiteServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sl)
    {
        $service = new MelisMarketPlaceSiteService();
        $service->setServiceLocator($sl);
        return $service;
    }
}
