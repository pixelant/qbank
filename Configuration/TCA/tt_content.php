<?php
defined('TYPO3_MODE') || die();
// Allow "qbank" online media type for tt_content assets (video).
$GLOBALS['TCA']['tt_content']['columns']['assets']['config']['foreign_selector_fieldTcaOverride']['config']['appearance']['elementBrowserAllowed'] .= ',qbank';
$GLOBALS['TCA']['tt_content']['columns']['assets']['config']['filter']['0']['parameters']['allowedFileExtensions'] .= ',qbank';
