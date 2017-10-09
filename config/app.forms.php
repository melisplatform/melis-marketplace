<?php
return array(
    'plugins' => array(
        'melis_market_place_tool_config' => array(
            'forms' => array(
                'melis_market_place_search' => array(
                    'attributes' => array(
                        'name' => 'melis_market_place_search_form',
                        'id' => 'melis_market_place_search_form',
                        'method' => 'POST',
                        'action' => '',
                    ),
                    'hydrator'  => 'Zend\Stdlib\Hydrator\ArraySerializable',
                    'elements'  => array(
                        array(
                            'spec' => array(
                                'name' => 'melis_market_place_search_input',
                                'type' => 'MelisText',
                                'options' => array(
                                ),
                                'attributes' => array(
                                    'id' => 'melis_market_place_search_input',
                                    'value' => '',
                                    'placeholder' => 'tr_market_place_search',
                                ),
                            ),
                        ),
                    )
                )
            )
        )
    )
);