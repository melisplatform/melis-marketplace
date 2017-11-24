<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisMarketPlace\Service\Factory;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;
use MelisMarketPlace\Service\MelisMarketPlaceComposerService;

class MelisMarketPlaceComposerServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sl)
    {
        $service = new MelisMarketPlaceComposerService();
        $service->setServiceLocator($sl);

        return $service;
    }

}