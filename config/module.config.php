<?php

namespace MelisMarketPlace;
use MelisMarketPlace\Support\MelisMarketPlace;
use MelisMarketPlace\Service\MelisMarketPlaceService;
use MelisMarketPlace\Service\Factory\AbstractFactory;
use MelisMarketPlace\Service\MelisMarketPlaceSiteService;

return [
    'router' => [
        'routes' => [
            'melis-backoffice' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/melis[/]',
                ],
                'child_routes' => [
                    'application-MelisMarketPlace' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => 'MelisMarketPlace',
                            'defaults' => [
                                '__NAMESPACE__' => 'MelisMarketPlace\Controller',
                                'controller' => 'MelisMarketPlace',
                                'action' => 'toolContainer',
                            ],
                        ],
                        // this route will be accessible in the browser by browsing
                        'may_terminate' => true,
                        'child_routes' => [
                            'default' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/[:controller[/:action]]',
                                    'constraints' => [
                                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                    ],
                                    'defaults' => [
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            /*
             * This route will handle the
             * alone setup of a module
             */
            'setup-melis-marketplace' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/MelisMarketPlace',
                    'defaults' => [
                        '__NAMESPACE__' => 'MelisMarketPlace\Controller',
                        'controller' => 'MelisSetup',
                        'action' => 'setup-form',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'default' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/[:controller[/:action]]',
                            'constraints' => [
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ],
                        ],
                    ],
                    'setup' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/setup',
                            'defaults' => [
                                'controller' => 'MelisMarketPlace\Controller\MelisSetup',
                                'action' => 'setup-form',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            MelisMarketPlaceService::class => AbstractFactory::class,
            MelisMarketPlaceSiteService::class => AbstractFactory::class,
        ],
        'aliases' => [
            'MelisMarketPlaceService' => MelisMarketPlaceService::class,
            'MelisMarketPlaceSiteService' => MelisMarketPlaceSiteService::class,
        ],
    ],
    'controllers' => [
        'invokables' => [
            'MelisMarketPlace\Controller\MelisMarketPlace' => \MelisMarketPlace\Controller\MelisMarketPlaceController::class,
            'MelisMarketPlace\Controller\MelisSetup' => \MelisMarketPlace\Controller\MelisSetupController::class,
        ],
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'template_map' => [

        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
    'asset_manager' => [
        'resolver_configs' => [
            'aliases' => [
                'MelisMarketPlace/' => __DIR__ . '/../public/',
            ],
        ],
    ],
];
