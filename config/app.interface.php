<?php
namespace MelisMarketPlace;

return array(
    'plugins' => array(
        'meliscore' => array(
            'interface' => array(
                'meliscore_leftmenu' => array(
                    'interface' => array(
                        'meliscore_toolstree' => array(
                            'interface' => array(
                                'melis_market_place_business_app_menu' => array(
                                    'conf' => array(
                                        'id'   => 'id_melis_market_place_business_app_menu',
                                        'name' => 'tr_market_place',
                                        'icon' =>  'fa fa-shopping-cart',
                                        'rights_checkbox_disable' => true,
                                    ),
                                    'interface' => array(
                                        // this will be the configuration of the tool.
                                        'melis_market_place_tool_config' => array(
                                            'conf' => array(
                                                'type' => '/melis_market_place_tool_config/interface/melis_market_place_tool_display',
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),

        /**
         * this is the configuration of the tool
         */
        'melis_market_place_tool_config' => array(
            'conf' => array(
                'name' => 'tr_market_place',
            ),
            'ressources' => array(
                'css' => array(
                    'MelisMarketPlace/css/melis-market-place.css',
                ),
                'js' => array(
                    'MelisMarketPlace/js/slick.min.js',
                    'MelisMarketPlace/js/melis-market-place.js',
                )
            ),
            'datas' => array(
                'development' => array(
                    'melis_packagist_server' => 'http://marketplace.melisplatform.com/melis-packagist'
                ),
                'default' => array(
                    'melis_packagist_server' => 'http://marketplace.melisplatform.com/melis-packagist'
                )

            ),
            'interface' => array(
                'melis_market_place_tool_display' => array(
                    'conf' => array(
                        'id'   => 'id_melis_market_place_tool_display',
                        'name' => 'tr_market_place',
                        'melisKey' => 'melis_market_place_tool_display',
                        'icon' => 'fa-shopping-cart',
                        'rights_checkbox_disable' => true
                    ),
                    'forward' => array(
                        'module' => 'MelisMarketPlace',
                        'controller' => 'MelisMarketPlace',
                         'action' => 'tool-container',
                        'jscallback' => 'fetchPackages();',
                        'jsdatas' => array()
                    ),
                ),
                'melis_market_place_tool_package_display' => array(
                    'conf' => array(
                        'id'   => 'id_melis_market_place_tool_package_display',
                        'name' => 'tr_market_place_single_view',
                        'melisKey' => 'melis_market_place_tool_package_display',
                        'icon' => 'fa-shopping-cart',
                        'rights_checkbox_disable' => true
                    ),
                    'forward' => array(
                        'module' => 'MelisMarketPlace',
                        'controller' => 'MelisMarketPlace',
                        'action' => 'tool-container-product-view',
                        'jscallback' => 'initSlick();',
                        'jsdatas' => array()
                    ),
                )
            ),

        )
    )
);