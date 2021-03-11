<?php

declare(strict_types=1);


namespace Pixelant\Qbank\FormEngine\Container;


use Pixelant\Qbank\Configuration\ExtensionConfigurationManager;
use Pixelant\Qbank\Utility\QbankUtility;
use TYPO3\CMS\Backend\Form\Container\InlineControlContainer;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Resource\Exception\InsufficientFolderAccessPermissionsException;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Inserts the "Add from QBank" button into file and media fields in the
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
            $selector = str_replace('</div><div class="help-block">', $button . '</div><div class="help-block">', $selector);
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

        $this->addJavaScriptConfiguration();

        $allowed =
            $inlineConfiguration['selectorOrUniqueConfiguration']['config']['appearance']['elementBrowserAllowed'];
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
                data-qbank-token="' . QbankUtility::getAccessToken() . '"
                disabled
                >
                ' . $this->iconFactory->getIcon('tx-qbank-logo', Icon::SIZE_SMALL)->render() . '
                ' . $buttonLabel .
            '</button>';

        return $button;
    }

    /**
     * Populates a configuration array that will be available in JavaScript as TYPO3.settings.FormEngineInline.qbank.
     *
     * Properties:
     *
     *   token - The qbank access token
     *
     */
    protected function addJavaScriptConfiguration()
    {
        if (isset($this->inlineData['qbank'])) {
            return;
        }

        /** @var ExtensionConfigurationManager $extensionConfigurationManager */
        $extensionConfigurationManager = GeneralUtility::makeInstance(ExtensionConfigurationManager::class);

        $configuration = [];
        $configuration['token'] = QbankUtility::getAccessToken();
        $configuration['host'] = $extensionConfigurationManager->getHost();

        $this->inlineData['qbank'] = $configuration;
    }

}
