<?php

namespace MelisMarketPlace;
return array(
    'router' => array(
        'routes' => array(
            'melis-backoffice' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/melis[/]',
                ),
                'child_routes' => array(
                    'application-MelisMarketPlace' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => 'MelisMarketPlace',
                            'defaults' => array(
                                '__NAMESPACE__' => 'MelisMarketPlace\Controller',
                                'controller'    => 'MelisMarketPlace',
                                'action'        => 'toolContainer',
                            ),
                        ),
                        // this route will be accessible in the browser by browsing
                        'may_terminate' => true,
                        'child_routes' => array(
                            'default' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/[:controller[/:action]]',
                                    'constraints' => array(
                                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                    ),
                                    'defaults' => array(
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            /*
             * This route will handle the
             * alone setup of a module
             */
            'setup-melis-marketplace' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/MelisMarketPlace',
                    'defaults' => array(
                        '__NAMESPACE__' => 'MelisMarketPlace\Controller',
                        'controller'    => 'MelisSetup',
                        'action'        => 'setup-form',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
//
                            ),
                        ),
                    ),
                    'setup' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/setup',
                            'defaults' => array(
                                'controller' => 'MelisMarketPlace\Controller\MelisSetup',
                                'action' => 'setup-form',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),

    'translator' => array(
        'locale' => 'en_EN',
    ),

    'service_manager' => array(
        'invokables' => array(

        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
        'factories' => array(
            'MelisMarketPlaceService' => 'MelisMarketPlace\Service\Factory\MelisMarketPlaceServiceFactory',
        ),
    ),

    'controllers' => array(
        'invokables' => array(
            'MelisMarketPlace\Controller\MelisMarketPlace' => 'MelisMarketPlace\Controller\MelisMarketPlaceController',
            'MelisMarketPlace\Controller\MelisSetup' => 'MelisMarketPlace\Controller\MelisSetupController',
        ),
    ),
    'controller_plugins' => array(
        'invokables' => array(
            //Dashboard Plugins
            'MelisMarketPlaceDashboardPluginMostDownloadedPackages' => 'MelisMarketPlace\Controller\DashboardPlugins\MelisMarketPlaceDashboardPluginMostDownloadedPackages',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'template_map' => array(
            //Dashboard Plugins
            'MelisMarketplaceDashboardPluginMostDownloadedPackages/most-downloaded-packages' => __DIR__ . '/../view/dashboard-plugins/marketplace-dashboard-plugin-most-downloaded-packages.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
    'asset_manager' => array(
        'resolver_configs' => array(
            'aliases' => array(
                'MelisMarketPlace/' => __DIR__ . '/../public/',
            ),
        ),
    ),
);