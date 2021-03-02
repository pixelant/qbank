<?php
defined('TYPO3_MODE') || die;

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'qbank',
    'Configuration/TypoScript',
    'QBank: connector for TYPO3 CMS'
);
