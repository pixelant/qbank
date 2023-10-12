<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Pixelant\Qbank\Xclass\Form\Container;

use Pixelant\Qbank\Configuration\ExtensionConfigurationManager;
use Pixelant\Qbank\Utility\QbankUtility;
use TYPO3\CMS\Backend\Form\Container\FilesControlContainer as CoreFilesControlContainer;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Resource\Filter\FileExtensionFilter;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Files entry container.
 *
 * Extends TYPO3\CMS\Backend\Form\Container\FilesControlContainer class to be able to add control to file.
 */
class FilesControlContainer extends CoreFilesControlContainer
{
    private const FILE_REFERENCE_TABLE = 'sys_file_reference';
    /**
     * Override function to add additional controls to 'file' TCA.
     * See file "vendor/typo3/cms-backend/Classes/Form/Container/FilesControlContainer.php" to
     * check how to configure button and folders etc.
     */
    protected function getFileSelectors(array $inlineConfiguration, FileExtensionFilter $fileExtensionFilter): array
    {
        // Get file selectors from parent first.
        $controls = parent::getFileSelectors($inlineConfiguration, $fileExtensionFilter);

        if (QbankUtility::getDownloadFolder() === null) {
            return $controls;
        }

        try {
            $accessToken = QbankUtility::getAccessToken();
        } catch (\Throwable $th) {
            return $controls;
        }

        $currentStructureDomObjectIdPrefix = $this->inlineStackProcessor->getCurrentStructureDomObjectIdPrefix(
            $this->data['inlineFirstPid']
        );
        $objectPrefix = $currentStructureDomObjectIdPrefix . '-' . self::FILE_REFERENCE_TABLE;

        $languageService = $this->getLanguageService();
        $extensionManager = $this->getExtensionConfigurationManager();

        $lllExtPath = 'LLL:EXT:qbank/Resources/Private/Language/locallang.xlf:';
        $buttonText = $lllExtPath . ':selector-button-control.label';
        $titleText = $lllExtPath . ':selector-button-control.title';
        $modalErrorTitle = $lllExtPath . ':js.modal.error-title';
        $modalRequestFailed = $lllExtPath . ':js.modal.request-failed';
        $modalRequestFailedError = $lllExtPath . ':js.modal.request-failed-error';
        $modalIllegalExtension = $lllExtPath . ':js.modal.illegal-extension';

        $buttonText = $languageService->sL($buttonText);
        $titleText = $languageService->sL($titleText);
        $modalErrorTitle = $languageService->sL($modalErrorTitle);
        $modalRequestFailed = $languageService->sL($modalRequestFailed);
        $modalRequestFailedError = $languageService->sL($modalRequestFailedError);
        $modalIllegalExtension = $languageService->sL($modalIllegalExtension);

        $attributes = [
            'type' => 'button',
            'class' => 'btn btn-default t3js-qbank-media-add-btn',
            'style' => !($inlineConfiguration['inline']['showCreateNewRelationButton'] ?? true) ? 'display: none;' : '',
            'title' => $titleText,
            'data-file-irre-object' => htmlspecialchars($objectPrefix),
            'data-file-allowed' => htmlspecialchars(
                implode(',', $fileExtensionFilter->getAllowedFileExtensions() ?? [])
            ),
            'data-qbank-host' => $extensionManager->getHost(),
            'data-qbank-deploymentsites' => implode(',', $extensionManager->getDeploymentSites() ?? []),
            'data-qbank-token' => $accessToken,
            'data-modal-error-title' => htmlspecialchars($modalErrorTitle),
            'data-modal-request-failed' => htmlspecialchars($modalRequestFailed),
            'data-modal-request-failed-error' => htmlspecialchars($modalRequestFailedError),
            'data-modal-illegal-extension' => $modalIllegalExtension,
        ];
        $controls[] = '
            <button ' . GeneralUtility::implodeAttributes($attributes, true) . '>
                ' . $this->iconFactory->getIcon('tx-qbank-logo', Icon::SIZE_SMALL)->render() . '
                ' . htmlspecialchars($buttonText) . '
            </button>';

        $this->javaScriptModules[] = JavaScriptModuleInstruction::create('@pixelant/qbank/qbank-media.js');

        return $controls;
    }

    /**
     * Get an instance of this extension's configuration manager.
     *
     * @return ExtensionConfigurationManager
     */
    protected function getExtensionConfigurationManager(): ExtensionConfigurationManager
    {
        /** @var ExtensionConfigurationManager $extensionConfigurationManager */
        $extensionConfigurationManager = GeneralUtility::makeInstance(ExtensionConfigurationManager::class);

        $languageField = $GLOBALS['TCA'][$this->data['tableName']]['ctrl']['languageField'];

        $languageId = -1;
        if ($languageField) {
            $languageId = (int)$this->data['databaseRow'][$languageField];
        }

        $pageId = $this->data['defaultLanguageRow']['pid'] ?? null;
        if ($this->data['tableName'] === 'pages') {
            $pageId = $this->data['defaultLanguageRow']['uid'] ?? null;
        }

        if ($pageId === null) {
            $pageId = $this->data['databaseRow']['pid'];
            if ($this->data['tableName'] === 'pages') {
                $pageId = $this->data['databaseRow']['uid'];
            }
        }

        $extensionConfigurationManager->configureForPage(
            (int)$pageId,
            $languageId
        );

        return $extensionConfigurationManager;
    }
}
