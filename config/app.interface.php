<?php

namespace MelisMarketPlace;

return [
    'plugins' => [
        'meliscore' => [
            'interface' => [
                'meliscore_header' => [
                    'interface' => [
                        'meliscore_header_close_all_tabs' => [],
                        'market_place_header_icon' => [
                            'conf' => [
                                'id' => 'id_market_place_header_icon',
                                'name' => 'tr_melis_platform_tracking_title',
                                'rightsDisplay' => "none",
                                'type' => 'market_place_header_icon',
                            ],
                        ],
                        'meliscore_header_flash_messenger' => [],
                        'meliscore_header_language' => [],
                        'meliscore_header_logout' => [],

                    ],
                ],
                'meliscore_leftmenu' => [
                    'interface' => [
                        'melismarketplace_toolstree_section' => [
                            'conf' => [
                                'is_parent_tool' => true,
                                'target_id' => 'id_melis_market_place_tool_display',
                                'target_meliskey' => 'melis_market_place_tool_display',
                                'tab_icon' => 'fa fa-shopping-cart',
                                'tab_name' => 'tr_market_place',
                            ]
                        ],
                    ],
                ],
            ],
        ],
        /*
         * Module header download-icon
         */
        'market_place_header_icon' => [
            'conf' => [
                'id' => 'id_market_place_header_icon',
                'rightsDisplay' => "none",
            ],
            'interface' => [
                'market_place_header_conf' => [
                    'conf' => [
                        'id' => 'id_market_place_header_conf',
                        'name' => 'tr_melis_link_market_place',
                        'melisKey' => 'market_place_header_conf',

                    ],
                    'forward' => [
                        'module' => 'MelisMarketPlace',
                        'controller' => 'MelisMarketPlace',
                        'action' => 'market-place-module-header',
                        'jscallback' => '',
                        'jsdatas' => [],
                    ],
                ],
            ],
        ],
        /**
         * this is the configuration of the tool
         */
        'melismarketplace_toolstree_section' => [
            'conf' => [
                'name' => 'tr_market_place',
                'rightsDisplay' => 'none',
            ],
            'ressources' => [
                'css' => [
                    '/MelisMarketPlace/css/slick.css',
                    '/MelisMarketPlace/css/custom-slick.css',
                    '/MelisMarketPlace/css/slick-theme.css',
                    '/MelisMarketPlace/css/melis-market-place.css',
                ],
                'js' => [
                    '/MelisMarketPlace/js/axios.min.js',
                    '/MelisMarketPlace/js/slick.min.js',
                    '/MelisMarketPlace/js/melis-market-place.js',
                ],
                /**
                 * the "build" configuration compiles all assets into one file to make
                 * lesser requests
                 */
                'build' => [
                    'disable_bundle' => false,
                    // lists of assets that will be loaded in the layout
                    'css' => [
                        '/MelisMarketPlace/build/css/bundle.css',
                    ],
                    'js' => [
                        '/MelisMarketPlace/build/js/bundle.js',
                    ],
                ],
            ],
            'datas' => [
                'melis_packagist_server' => 'http://marketplace.melisplatform.com/melis-packagist',
                'exceptions' => ['MelisCore', 'MelisEngine', 'MelisFront', 'MelisAssetManager', 'MelisComposerDeploy', 'MelisDbDeploy'],
            ],
            'interface' => [
                'melis_market_place_tool_display' => [
                    'conf' => [
                        'id' => 'id_melis_market_place_tool_display',
                        'name' => 'tr_market_place',
                        'melisKey' => 'melis_market_place_tool_display',
                        'icon' => 'fa-shopping-cart',
                        'rightsDisplay' => 'none',
                        'rights_checkbox_disable' => true,
                    ],
                    'forward' => [
                        'module' => 'MelisMarketPlace',
                        'controller' => 'MelisMarketPlace',
                        'action' => 'tool-container',
                        'jscallback' => 'fetchPackages();',
                        'jsdatas' => [],
                    ],
                ],
                'melis_market_place_tool_package_display' => [
                    'conf' => [
                        'id' => 'id_melis_market_place_tool_package_display',
                        'name' => 'tr_market_place_single_view',
                        'melisKey' => 'melis_market_place_tool_package_display',
                        'icon' => 'fa-shopping-cart',
                        'rightsDisplay' => 'none',
                        'rights_checkbox_disable' => true,
                    ],
                    'forward' => [
                        'module' => 'MelisMarketPlace',
                        'controller' => 'MelisMarketPlace',
                        'action' => 'tool-container-product-view',
                        'jscallback' => '',
                        'jsdatas' => [],
                    ],
                ],
                'melis_market_place_tool_package_modal_container' => [
                    'conf' => [
                        'id' => 'id_melis_market_place_tool_package_modal_container',
                        'name' => 'tr_melis_market_place_tool_package_modal_container',
                        'melisKey' => 'melis_market_place_tool_package_modal_container',
                        'rightsDisplay' => 'none',
                    ],
                    'forward' => [
                        'module' => 'MelisMarketPlace',
                        'controller' => 'MelisMarketPlace',
                        'action' => 'tool-product-modal-container',
                        'jscallback' => '',
                        'jsdatas' => [],
                    ],
                    'interface' => [
                        'melis_market_place_tool_package_modal_content' => [
                            'conf' => [
                                'id' => 'id_melis_market_place_tool_package_modal_content',
                                'melisKey' => 'melis_market_place_tool_package_modal_content',
                                'name' => 'tr_melis_market_place_tool_package_modal_content',
                            ],
                            'forward' => [
                                'module' => 'MelisMarketPlace',
                                'controller' => 'MelisMarketPlace',
                                'action' => 'tool-product-modal-content',
                                'jscallback' => '',
                                'jsdatas' => [],
                            ],
                        ],
                        'melis_market_place_module_setup_form_content' => [
                            'conf' => [
                                'id' => 'id_melis_market_place_module_setup_form_content',
                                'melisKey' => 'melis_market_place_module_setup_form_content',
                                'name' => 'tr_melis_market_place_tool_package_modal_content',
                            ],
                            'forward' => [
                                'module' => 'MelisMarketPlace',
                                'controller' => 'MelisMarketPlace',
                                'action' => 'tool-module-form-setup-content',
                                'jscallback' => '',
                                'jsdatas' => [],
                            ],
                        ],
                    ],
                ],
            ],

        ],

    ],
];
