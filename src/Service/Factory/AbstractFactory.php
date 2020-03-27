<?php
namespace MelisMarketPlace\Service\Factory;
use Interop\Container\ContainerInterface;

class AbstractFactory
{
    public function __invoke(ContainerInterface $container, $requestedName)
    {
        $instance = new $requestedName();
        $instance->setServiceManager($container);
        return $instance;
    }
}