<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'QBank DAM',
    'description' => 'Integration of QBank DAM for TYPO3',
    'category' => 'plugin',
    'author' => 'Pixelant',
    'author_email' => 'info@pixelant.net',
    'author_company' => 'Pixelant',
    'state' => 'stable',
    'createDirs' => '',
    'clearCacheOnLoad' => true,
    'version' => '1.2.1',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-11.5.99',
        ],
        'conflicts' => [],
        'suggests' => [
            'filemetadata' => '10.4.0-11.5.99',
        ]
    ]
];
