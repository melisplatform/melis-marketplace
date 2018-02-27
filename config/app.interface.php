<?php
namespace MelisMarketPlace;

return array(
    'plugins' => array(
        'meliscore' => array(
            'interface' => array(
                'meliscore_header' => array(
                    'interface' => array(
                        'meliscore_header_close_all_tabs' => array(),
                        'market_place_header_icon' => array(
                            'conf' => array(
                                'id' => 'id_market_place_header_icon',
                                'name' => 'tr_melis_platform_tracking_title',
                                'rightsDisplay' => "none",
                                'type' => 'market_place_header_icon'
                            ),
                        ),
                        'meliscore_header_flash_messenger' => array(),
                        'meliscore_header_language' => array(),
                        'meliscore_header_logout' => array(),

                    ),
                ),
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
        /*
         * Module header download-icon
         */
        'market_place_header_icon' => array(
            'conf' => array(
                'id' => 'id_market_place_header_icon',
                'rightsDisplay' => "none",
            ),
            'interface' => array(
                ',market_place_header_conf' => array(
                    'conf' => array(
                        'id' => 'id_market_place_header_conf',
                        'name' => 'tr_melis_link_market_place',
                        'melisKey' => 'market_place_header_conf',

                    ),
                    'forward' => array(
                        'module' => 'MelisMarketPlace',
                        'controller' => 'MelisMarketPlace',
                        'action' => 'market-place-module-header',
                        'jscallback' => '',
                        'jsdatas' => array()
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
                    '/MelisMarketPlace/css/melis-market-place.css',
                ),
                'js' => array(
                    '/MelisMarketPlace/js/slick.min.js',
                    '/MelisMarketPlace/js/FileSaver/FileSaver.min.js',
                    '/MelisMarketPlace/js/melis-market-place.js',
                )
            ),
            'datas' => array(
                'melis_packagist_server' => 'http://marketplace.melisplatform.com/melis-packagist',
                'exceptions' => array('MelisCore', 'MelisEngine', 'MelisFront', 'MelisAssetManager', 'MelisComposerDeploy', 'MelisDbDeploy')
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
                        'jscallback' => '',
                        'jsdatas' => array()
                    ),
                ),
                'melis_market_place_tool_package_modal_container' => array(
                    'conf' => array(
                        'id'   => 'id_melis_market_place_tool_package_modal_container',
                        'name' => 'tr_melis_market_place_tool_package_modal_container',
                        'melisKey' => 'melis_market_place_tool_package_modal_container',
                    ),
                    'forward' => array(
                        'module' => 'MelisMarketPlace',
                        'controller' => 'MelisMarketPlace',
                        'action' => 'tool-product-modal-container',
                        'jscallback' => '',
                        'jsdatas' => array()
                    ),
                    'interface' => array(
                        'melis_market_place_tool_package_modal_content' => array(
                            'conf' => array(
                                'id' => 'id_melis_market_place_tool_package_modal_content',
                                'melisKey' => 'melis_market_place_tool_package_modal_content',
                                'name' => 'tr_melis_market_place_tool_package_modal_content'
                            ),
                            'forward' => array(
                                'module' => 'MelisMarketPlace',
                                'controller' => 'MelisMarketPlace',
                                'action' => 'tool-product-modal-content',
                                'jscallback' => '',
                                'jsdatas' => array()
                            ),
                        )
                    )
                )
            ),

        ),
        'meliscore_dashboard' => array(
            'interface' => array(
                'market_place_most_downloaded_modules' => array(
                    'conf' => array(
                        'id' => 'id_market_place_most_downloaded_modules',
                        'name' => 'tr_market_place_most_downloaded_modules_title',
                        'melisKey' => 'market_place_most_downloaded_modules',
                        'width' => 6,
                        'height' => 'dashboard-large',

                    ),
                    'forward' => array(
                        'module' => 'MelisMarketplace',
                        'controller' => 'MelisMarketPlace',
                        'action' => 'market-place-dashboard',
                        'jscallback' => '',
                        'jsdatas' => array()
                    ),
                ),
            )
        ),

    ),
);