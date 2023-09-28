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

use TYPO3\CMS\Backend\Form\Container\FilesControlContainer as CoreFilesControlContainer;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Resource\Filter\FileExtensionFilter;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Files entry container.
 *
 * Extends TYPO3\CMS\Backend\Form\Container\FilesControlContainer class to be able to add control to file.
 */
class FilesControlContainer extends CoreFilesControlContainer
{
    /**
     * Override function to add additional controls to 'file' TCA.
     * See file "vendor/typo3/cms-backend/Classes/Form/Container/FilesControlContainer.php" to
     * check how to configure button and folders etc.
     */
    protected function getFileSelectors(array $inlineConfiguration, FileExtensionFilter $fileExtensionFilter): array
    {
        $languageService = $this->getLanguageService();

        $controls = parent::getFileSelectors($inlineConfiguration, $fileExtensionFilter);
        $objectPrefix = 'qbank-prefix';
        $buttonText = 'LLL:EXT:qbank/Resources/Private/Language/locallang.xlf:selector-button-control.label';
        $buttonText = $languageService->sL($buttonText);
        $attributes = [
            'type' => 'button',
            'class' => 'btn btn-default t3js-element-browser',
            'style' => !($inlineConfiguration['inline']['showCreateNewRelationButton'] ?? true) ? 'display: none;' : '',
            'title' => $buttonText,
            'data-mode' => 'qbank',
            'data-params' => '|||allowed=' . implode(',', $fileExtensionFilter->getAllowedFileExtensions() ?? []) . ';disallowed=' . implode(',', $fileExtensionFilter->getDisallowedFileExtensions() ?? []) . '|' . $objectPrefix,
        ];
        $controls[] = '
            <button ' . GeneralUtility::implodeAttributes($attributes, true) . '>
                ' . $this->iconFactory->getIcon('actions-insert-record', Icon::SIZE_SMALL)->render() . '
                ' . htmlspecialchars($buttonText) . '
            </button>';

        // $this->javaScriptModules[] = JavaScriptModuleInstruction::create('@typo3/backend/online-media.js');

        return $controls;
    }
}
