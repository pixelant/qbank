<?php

defined('TYPO3_MODE') || die;

(function () {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['qbank_media_usage_reporter']
        = \Pixelant\Qbank\Hook\MediaUsageReporterDataHandlerHook::class;

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['qbank_media_usage_reporter']
        = \Pixelant\Qbank\Hook\MediaUsageReporterDataHandlerHook::class;
})();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
    'file',
    'qbank',
    '',
    '',
    [
        'routeTarget' => \Pixelant\Qbank\Controller\ManagementController::class . '::handleRequest',
        'access' => 'group,user',
        'name' => 'file_qbank',
        'icon' => 'EXT:qbank/Resources/Public/Icons/Extension.svg',
        'labels' => 'LLL:EXT:qbank/Resources/Private/Language/locallang_module_qbank.xlf',
        'navigationComponentId' => '',
        'inheritNavigationComponentFromMainModule' => false,
    ]
);
