<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisMarketPlace\Listener;

use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;

class MelisMarketPlaceTestListener extends AbstractListenerAggregate implements ListenerAggregateInterface
{
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $sharedEvents = $events->getSharedManager();

        $callBackHandler = $sharedEvents->attach(
            '*', 'melis_marketplace_site_intall_test',
            function ($e) {
                $params = $params = $e->getParams();
                $logPath = $_SERVER['DOCUMENT_ROOT'] . '/../cache/marketplace.log';
                file_put_contents($logPath, print_r($params, true) . PHP_EOL . PHP_EOL, FILE_APPEND);
            },
            -10000);

        $this->listeners[] = $callBackHandler;
    }
}
