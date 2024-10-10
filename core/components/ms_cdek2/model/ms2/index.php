<?php

$this->modx->lexicon->load('ms_cdek2:default');

return [
    'map' => [
        'msProductData' => [
            'fields' => [
                'mscdek_size' => null,
            ],
            'fieldMeta' => [
                'mscdek_size' => [
                    'dbtype' => 'varchar',
                    'precision' => '255',
                    'phptype' => 'string',
                    'null' => true,
                    'default' => '',
                ]
            ]
        ],
        'msOrderAddress' => [
            'fields' => [
                'point' => NULL,
                'cdek_id' => NULL,
                'inner_cdek_id' => NULL,
                'cdek_tariff_id' => NULL,
                'pvz_id' => NULL,
            ],
            'fieldMeta' => [
                'point' => [
                    'dbtype' => 'varchar',
                    'precision' => '100',
                    'phptype' => 'string',
                    'null' => true,
                ],
                'cdek_id' => [
                    'dbtype' => 'int',
                    'precision' => '10',
                    'phptype' => 'integer',
                    'null' => true,
                ],
                'inner_cdek_id' => [
                    'dbtype' => 'int',
                    'precision' => '10',
                    'phptype' => 'integer',
                    'null' => true,
                ],
                'cdek_tariff_id' => [
                    'dbtype' => 'int',
                    'precision' => '10',
                    'phptype' => 'integer',
                    'null' => true,
                ],
                'pvz_id' => [
                    'dbtype' => 'varchar',
                    'precision' => '255',
                    'phptype' => 'string',
                    'null' => true,
                ],
            ],
        ],
    ],
    'manager' => [
        'msProductData' => MODX_ASSETS_URL . 'components/ms_cdek2/js/mgr/msproductdata.js',
    ],
];