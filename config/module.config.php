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
            'MelisMarketPlaceComposerService' => 'MelisMarketPlace\Service\Factory\MelisMarketPlaceComposerServiceFactory',
        ),
    ),

    'controllers' => array(
        'invokables' => array(
            'MelisMarketPlace\Controller\MelisMarketPlace' => 'MelisMarketPlace\Controller\MelisMarketPlaceController',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'template_map' => array(
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