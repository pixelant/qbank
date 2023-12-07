<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'QBank DAM',
    'description' => 'Integration of QBank DAM for TYPO3',
    'category' => 'plugin',
    'author' => 'Pixelant',
    'author_email' => 'info@pixelant.net',
    'author_company' => 'Pixelant',
    'state' => 'stable',
    'version' => '2.0.3',
    'constraints' => [
        'depends' => [
            'typo3' => '12.0.0-12.4.99',
        ],
        'conflicts' => [],
        'suggests' => [
            'filemetadata' => '12.0.0-12.4.99',
        ]
    ]
];
