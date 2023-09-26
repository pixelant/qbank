<?php

use Pixelant\Qbank\Controller\ManagementController;

return [
    'file_qbank' => [
        'parent' => 'file',
        'position' => ['after' => 'file_list'],
        'access' => 'group,user',
        'workspaces' => 'live',
        'path' => '/file/qbank',
        'icon' => 'EXT:qbank/Resources/Public/Icons/Extension.svg',
        'labels' => 'LLL:EXT:qbank/Resources/Private/Language/locallang_module_qbank.xlf',
        'navigationComponentId' => '',
        'inheritNavigationComponentFromMainModule' => false,
        'extensionName' => 'Qbank',
        'controllerActions' => [
            ManagementController::class => [
                'list',
            ],
        ],
    ]
];
