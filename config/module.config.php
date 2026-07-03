<?php

namespace MelisMarketPlace;
use MelisMarketPlace\Support\MelisMarketPlace;
use MelisMarketPlace\Service\MelisMarketPlaceService;
use MelisCore\Service\Factory\AbstractFactory;
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
                            /*
                             * JSON API for the native React "Market Place" list (browse-only —
                             * install/update/remove stay on the legacy tool). Own routes (not in
                             * melis-react-api) so the module owns its React API end to end.
                             */
                            'react-api-packages' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/react-api/packages',
                                    'defaults' => [
                                        '__NAMESPACE__' => 'MelisMarketPlace\Controller',
                                        'controller' => 'MelisMarketPlaceReactApi',
                                        'action' => 'packages',
                                    ],
                                ],
                            ],
                            'react-api-package-detail' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/react-api/packages/:id',
                                    'constraints' => ['id' => '[0-9]+'],
                                    'defaults' => [
                                        '__NAMESPACE__' => 'MelisMarketPlace\Controller',
                                        'controller' => 'MelisMarketPlaceReactApi',
                                        'action' => 'get',
                                    ],
                                ],
                            ],
                            'react-api-groups' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/react-api/groups',
                                    'defaults' => [
                                        '__NAMESPACE__' => 'MelisMarketPlace\Controller',
                                        'controller' => 'MelisMarketPlaceReactApi',
                                        'action' => 'groups',
                                    ],
                                ],
                            ],
                            'react-api-stats' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/react-api/stats',
                                    'defaults' => [
                                        '__NAMESPACE__' => 'MelisMarketPlace\Controller',
                                        'controller' => 'MelisMarketPlaceReactApi',
                                        'action' => 'stats',
                                    ],
                                ],
                            ],
                            'react-api-status' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/react-api/status',
                                    'defaults' => [
                                        '__NAMESPACE__' => 'MelisMarketPlace\Controller',
                                        'controller' => 'MelisMarketPlaceReactApi',
                                        'action' => 'status',
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
            'MelisMarketPlace\Controller\MelisMarketPlaceReactApi' => \MelisMarketPlace\Controller\MelisMarketPlaceReactApiController::class,
        ],
    ],
    'view_manager' => [
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
