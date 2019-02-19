<?php

namespace MelisMarketPlace;
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
                            'defaults' => [
//
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

    'translator' => [
        'locale' => 'en_EN',
    ],

    'service_manager' => [
        'invokables' => [

        ],
        'aliases' => [
            'translator' => 'MvcTranslator',
        ],
        'factories' => [
            'MelisMarketPlaceService' => \MelisMarketPlace\Service\Factory\MelisMarketPlaceServiceFactory::class,
            'MelisMarketPlaceSiteService' => \MelisMarketPlace\Service\Factory\MelisMarketPlaceSiteServiceFactory::class,
        ],
    ],

    'controllers' => [
        'invokables' => [
            'MelisMarketPlace\Controller\MelisMarketPlace' => 'MelisMarketPlace\Controller\MelisMarketPlaceController',
            'MelisMarketPlace\Controller\MelisSetup' => 'MelisMarketPlace\Controller\MelisSetupController',
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
