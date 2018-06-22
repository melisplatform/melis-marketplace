<?php
return array(
    'plugins' => array(
        'melismarketplace' => array(
            'ressources' => array(
                'css' => array(

                ),
                'js' => array(

                ),
            ),
            'dashboard_plugins' => array(
                'MelisMarketPlaceDashboardPluginMostDownloadedPackages' => array(
                    'plugin_id' => 'MelisMarketPlaceDashboardPluginMostDownloadedPackages',
                    'name' => 'tr_melis_marketplace_dashboard_plugin_most_downloaded_packages',
                    'description' => 'tr_melis_marketplace_dashboard_plugin_most_downloaded_packages_description',
                    'icon' => 'fa fa-shopping-cart',
                    'thumbnail' => '/MelisMarketPlace/images/MelisMarketPlaceDashboardPluginMostDownloadedPackages.jpg',
                    'jscallback' => '',
                    'width' => 6,
                    'height' => 8,

                    'interface' => array(
                        'melis_marketplace_dashboard_plugin_most_downloaded_packages' => array(
                            'forward' => array(
                                'module' => 'MelisMarketPlace',
                                'plugin' => 'MelisMarketPlaceDashboardPluginMostDownloadedPackages',
                                'function' => 'mostDownloadedPackages'
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
);