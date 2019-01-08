<?php
return [
    'plugins' => [
        'melis_market_place_tool_config' => [
            'forms' => [
                'melis_market_place_search' => [
                    'attributes' => [
                        'name' => 'melis_market_place_search_form',
                        'id' => 'melis_market_place_search_form',
                        'method' => 'POST',
                        'action' => '',
                    ],
                    'hydrator' => 'Zend\Stdlib\Hydrator\ArraySerializable',
                    'elements' => [
                        [
                            'spec' => [
                                'name' => 'melis_market_place_search_input',
                                'type' => 'MelisText',
                                'options' => [
                                ],
                                'attributes' => [
                                    'id' => 'melis_market_place_search_input',
                                    'value' => '',
                                    'placeholder' => 'tr_market_place_search',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
