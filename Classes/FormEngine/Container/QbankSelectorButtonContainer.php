<?php

declare(strict_types=1);

namespace Pixelant\Qbank\FormEngine\Container;

use Pixelant\Qbank\Configuration\ExtensionConfigurationManager;
use Pixelant\Qbank\Utility\QbankUtility;
use TYPO3\CMS\Backend\Form\Container\InlineControlContainer;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Inserts the "Add from QBank" button into file and media fields in the.
 */
class QbankSelectorButtonContainer extends InlineControlContainer
{
    /**
     * @param array $inlineConfiguration
     * @return string
     */
    protected function renderPossibleRecordsSelectorTypeGroupDB(array $inlineConfiguration): string
    {
        $selector = parent::renderPossibleRecordsSelectorTypeGroupDB($inlineConfiguration);

        $button = $this->renderQbankButton($inlineConfiguration);

        // Inject button before help-block
        if (strpos($selector, '</div><div class="help-block">') > 0) {
            $selector = str_replace(
                '</div><div class="help-block">',
                $button . '</div><div class="help-block">',
                $selector
            );
        // Try to inject it into the form-control container
        } elseif (preg_match('/<\/div><\/div>$/i', $selector)) {
            $selector = preg_replace('/<\/div><\/div>$/i', $button . '</div></div>', $selector);
        } else {
            $selector .= $button;
        }

        return $selector;
    }

    /**
     * @param array $inlineConfiguration
     * @return string
     */
    protected function renderQbankButton(array $inlineConfiguration): string
    {
        if (QbankUtility::getDownloadFolder() === null) {
            return '';
        }

        try {
            $accessToken = QbankUtility::getAccessToken();
        } catch (\Throwable $th) {
            return '';
        }

        $this->addJavaScriptConfiguration($accessToken);
        $this->javaScriptLocalization();

        $appearanceConfiguration = $inlineConfiguration['selectorOrUniqueConfiguration']['config']['appearance'];
        
        $allowed = $appearanceConfiguration['qbankBrowserAllowed'] ?? $appearanceConfiguration['elementBrowserAllowed'];
        
        $allowedArray = GeneralUtility::trimExplode(',', $allowed, true);
        if (empty($allowedArray)) {
            $allowedArray = GeneralUtility::trimExplode(',', $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'], true);
        }
        $allowed = implode(',', $allowedArray);

        $currentStructureDomObjectIdPrefix = $this->inlineStackProcessor->getCurrentStructureDomObjectIdPrefix(
            $this->data['inlineFirstPid']
        );
        $objectPrefix = $currentStructureDomObjectIdPrefix . '-' . $inlineConfiguration['foreign_table'];

        $this->requireJsModules[] = 'TYPO3/CMS/Qbank/Qbank';

        $buttonLabel = htmlspecialchars(LocalizationUtility::translate('selector-button-control.label', 'qbank'));
        $titleText = htmlspecialchars(LocalizationUtility::translate('selector-button-control.title', 'qbank'));

        $button = '
            <button type="button" class="btn btn-default t3js-qbank-selector-btn"
                data-title="' . htmlspecialchars($titleText) . '"
                data-file-irre-object="' . htmlspecialchars($objectPrefix) . '"
                data-file-allowed="' . htmlspecialchars($allowed) . '"
                data-qbank-token="' . $accessToken . '"
                disabled
                >
                ' . $this->iconFactory->getIcon('tx-qbank-logo', Icon::SIZE_SMALL)->render() . '
                ' . $buttonLabel .
            '</button>';

        return $button;
    }

    /**
     * Adds localization string for JavaScript use.
     *
     * @return void
     */
    protected function javaScriptLocalization(): void
    {
        /** @var PageRenderer $pageRenderer */
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);

        $pageRenderer->addInlineLanguageLabelArray([
            'qbank.modal.error-title' => $this->translate('js.modal.error-title'),
            'qbank.modal.request-failed' => $this->translate('js.modal.request-failed'),
            'qbank.modal.request-failed-error' => $this->translate('js.modal.request-failed-error'),
            'qbank.modal.illegal-extension' => $this->translate('js.modal.illegal-extension'),
        ]);
    }

    /**
     * Populates a configuration array that will be available in JavaScript as TYPO3.settings.FormEngineInline.qbank.
     *
     * Properties:
     *
     *   token - The qbank access token
     *
     * @param string $accessToken
     * @return void
     */
    protected function addJavaScriptConfiguration(string $accessToken): void
    {
        $extensionConfigurationManager = $this->getExtensionConfigurationManager();

        $configuration = [
            'token' => $accessToken,
            'host' => $extensionConfigurationManager->getHost(),
            'deploymentSites' => $extensionConfigurationManager->getDeploymentSites(),
        ];

        /** @var PageRenderer $pageRenderer */
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);

        $pageRenderer->addInlineSettingArray('qbank', $configuration);
    }

    /**
     * Returns a translated string for $key.
     *
     * @param $key
     * @return string|null
     */
    protected function translate(string $key): ?string
    {
        return LocalizationUtility::translate($key, 'qbank');
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
            $languageId = (int)$this->data['databaseRow'][$languageField][0];
        }

        $pageId = $this->data['defaultLanguageRow']['pid'];
        if ($this->data['tableName'] === 'pages') {
            $pageId = $this->data['defaultLanguageRow']['uid'];
        }

        if ($pageId === null) {
            $pageId = $this->data['databaseRow']['pid'];
            if ($this->data['tableName'] === 'pages') {
                $pageId = $this->data['databaseRow']['uid'];
            }
        }

        $extensionConfigurationManager->configureForPage(
            $pageId,
            $languageId
        );

        return $extensionConfigurationManager;
    }
}
