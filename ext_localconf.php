<?php
defined('TYPO3_MODE') || die('Access denied.');
$_EXTKEY = 'qbank';
// Register QBank File Browser
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ElementBrowsers']['qbankfile'] = 'Pixelant\\Qbank\\Browser\\QbankFileBrowser';

// Register qbank media service
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['onlineMediaHelpers']['qbank'] = \Pixelant\Qbank\OnlineMedia\Helpers\QbankHelper::class;
$rendererRegistry = \TYPO3\CMS\Core\Resource\Rendering\RendererRegistry::getInstance();
$rendererRegistry->registerRendererClass(
    \Pixelant\Qbank\Rendering\QbankRenderer::class
);

// Register an custom mime-type for QBank videos
$GLOBALS['TYPO3_CONF_VARS']['SYS']['FileInfo']['fileExtensionToMimeType']['qbank'] = 'video/qbank';

// Register your qbank file extension as allowed media file
$GLOBALS['TYPO3_CONF_VARS']['SYS']['mediafile_ext'] .= ',qbank';


// Extend ResourceFactory so we can add QBank media usages for public url:s
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Core\\Resource\\ResourceFactory'] = array('className' => \Pixelant\Qbank\Resource\QbankResourceFactory::class);

// Hook to DataHandler, deleteAction to remove QBank usage if set.
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][] = \Pixelant\Qbank\Hook\DataHandlerHook::class;

# Cache settings
if (!is_array($TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['qbank_connector'])) {
    $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['qbank_connector'] = array(
        'backend' => 'TYPO3\CMS\Core\Cache\Backend\FileBackend',
        'options' => array(
            'defaultLifetime' => 3600*24,
        ),
    );
}

// Add QBank media usage report scheduler task
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\Pixelant\Qbank\Task\QbankReportUsageTask::class] = array(
        'extension' => $_EXTKEY,
        'title' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xlf:qbankReportUsageTask.name',
        'description' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xlf:qbankReportUsageTask.description'
);
