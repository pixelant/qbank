<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function()
    {
    	if (TYPO3_MODE === 'BE') {
	        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
	            'Pixelant.Qbank',
	            'file', // Make module a submodule of 'file'
	            'media', // Submodule key
	            '', // Position
	            [
	                'QbankMedia' => 'list, show',
	            ],
	            [
	                'access' => 'user,group',
	                'workspaces' => 'online,custom',
	                'icon'   => 'EXT:qbank/Resources/Public/Icons/user_mod_media.svg',
	                'labels' => 'LLL:EXT:qbank/Resources/Private/Language/locallang_media.xlf',
                	'inheritNavigationComponentFromMainModule' => false
	            ]
	        );

	    }
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_qbank_domain_model_qbankmedia', 'EXT:qbank/Resources/Private/Language/locallang_csh_tx_qbank_domain_model_qbankmedia.xlf');
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_qbank_domain_model_qbankmedia');

    }
);
