<?php

return [

    'plugins' => [
        'diagnostic' => [
            'conf' => [
                // user rights exclusions
                'rightsDisplay' => 'none',
            ],
            'MelisMarketPlace' => [
                'testFolder' => 'test',
                'moduleTestName' => 'MelisMarketPlaceTest',
                'db' => [],
                'methods' => [
                    'testGetPackages' => [
                        'payloads' => [

                        ],
                    ],
                ],
            ],
        ],
    ],


];

