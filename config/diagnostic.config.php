<?php

return array(

    'plugins' => array(
        'diagnostic' => array(
            'conf' => array(
                // user rights exclusions
                'rightsDisplay' => 'none',
            ),
            'MelisMarketPlace' => array(
                'testFolder' => 'test',
                'moduleTestName' => 'MelisMarketPlaceTest',
                'db' => array(),
                'methods' => array(
                    'testGetPackages' => array(
                        'payloads' => array(

                        ),
                    ),
                ),
            ),
        ),
    ),


);

