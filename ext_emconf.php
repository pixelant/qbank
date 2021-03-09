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
    'version' => '0.0.0-dev',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-10.4.99'
        ],
        'conflicts' => [],
        'suggests' => []
    ]
];
