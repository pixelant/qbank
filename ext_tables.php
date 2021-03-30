<?php
defined('TYPO3_MODE') || die;

(function () {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['qbank_media_usage_reporter']
        = \Pixelant\Qbank\Hook\ProcessDatamap\MediaUsageReporterProcessDatamap::class;
})();
