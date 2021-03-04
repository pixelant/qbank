<?php
defined('TYPO3_MODE') || die();
// Enable QBank button for tt_content column assets as a customControl in TCA.
$GLOBALS['TCA']['tt_content']['columns']['assets']['config']['customControls']['qbank'] = [
    'userFunc' => \Pixelant\Qbank\CustomControls\QbankCustomControl::class . '->render',
];
// Allow "qbank" online media type for tt_content assets (video).
$GLOBALS['TCA']['tt_content']['columns']['assets']['config']['foreign_selector_fieldTcaOverride']['config']['appearance']['elementBrowserAllowed'] .= ',qbank';
$GLOBALS['TCA']['tt_content']['columns']['assets']['config']['filter']['0']['parameters']['allowedFileExtensions'] .= ',qbank';
$GLOBALS['TCA']['tt_content']['columns']['image']['config']['customControls']['qbank'] = [
    'userFunc' => \Pixelant\Qbank\CustomControls\QbankCustomControl::class . '->render',
];
