<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Qbank',
    'description' => 'Connects QBank version 3 to TYPO3',
    'category' => 'plugin',
    'author' => '',
    'author_email' => '',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-10.99.99',
            'filemetadata' => '10.4.0-10.99.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'autoload' => [
        'classmap' => [
            'Pixelant\\Qbank\\' => 'Classes',
            'QBNK\\QBank\\API\\' => 'Contrib/qbnk/qbank3api-phpwrapper',
        ],
    ],
];
