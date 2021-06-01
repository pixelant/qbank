<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:qbank/Resources/Private/Language/locallang_db.xlf:tx_qbank_domain_model_mapping',
        'label' => 'source_property',
        'label_alt' => 'target_property',
        'label_alt_force' => true,
        'crdate' => 'createdon',
        'cruser_id' => 'createdby',
        'tstamp' => 'updatedon',
        'versioningWS' => false,
        'default_sortby' => 'source_property, target_property',
        'rootLevel' => 1,
        'security' => [
            'ignoreWebMountRestriction' => true,
            'ignoreRootLevelRestriction' => true,
        ],
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'disabled',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'typeicon_classes' => [
            'default' => 'mimetypes-x-qbank-mapping',
        ],
        'searchFields' => 'source_property, target_property',
    ],
    'types' => [
        '1' => [
            'showitem' => '
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, --palette--;;properties,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, --palette--;;visibility',
        ],
    ],
    'palettes' => [
        'visibility' => [
            'showitem' => 'disabled, --linebreak--, starttime, endtime',
        ],
        'properties' => [
            'showitem' => 'source_property, --linebreak--, target_property',
        ],
    ],
    'columns' => [
        'disabled' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.enabled',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        0 => '',
                        1 => '',
                        'invertStateDisplay' => true,
                    ],
                ],
            ],
        ],
        'starttime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime,int',
                'default' => 0,
            ],
        ],
        'endtime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime,int',
                'default' => 0,
                'range' => [
                    'upper' => mktime(0, 0, 0, 1, 1, 2038),
                ],
            ],
        ],
        'source_property' => [
            'label' => 'LLL:EXT:qbank/Resources/Private/Language/locallang_db.xlf:tx_qbank_domain_model_mapping.source_property',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'minitems' => 1,
                'maxitems' => 1,
                'size' => 1,
                'default' => '',
                'items' => [
                    ['', ''],
                ],
                'itemsProcFunc' => Pixelant\Qbank\FormEngine\QbankProperyItemsProcFunc::class . '->itemsProcFunc',
            ],
        ],
        'target_property' => [
            'exclude' => true,
            'label' => 'LLL:EXT:qbank/Resources/Private/Language/locallang_db.xlf:sys_redirect.target_property',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'minitems' => 1,
                'maxitems' => 1,
                'size' => 1,
                'default' => '',
                'items' => [
                    [
                        'LLL:EXT:qbank/Resources/Private/Language/locallang_module_qbank.xlf:sys_file.name',
                        'sys_file.name',
                    ],
                    [
                        'LLL:EXT:qbank/Resources/Private/Language/locallang_module_qbank.xlf:sys_file_metadata.description',
                        'sys_file_metadata.description',
                    ],
                    [
                        'LLL:EXT:qbank/Resources/Private/Language/locallang_module_qbank.xlf:sys_file_metadata.alternative',
                        'sys_file_metadata.alternative',
                    ],
                ],
                'itemsProcFunc' => Pixelant\Qbank\FormEngine\Typo3ProperyItemsProcFunc::class . '->itemsProcFunc',
            ],
        ],
    ],
];
