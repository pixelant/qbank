<?php
defined('TYPO3') or die();

call_user_func(static function () {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['qbank_media_usage_reporter']
        = \Pixelant\Qbank\Hook\MediaUsageReporterDataHandlerHook::class;

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['qbank_media_usage_reporter']
        = \Pixelant\Qbank\Hook\MediaUsageReporterDataHandlerHook::class;
});
