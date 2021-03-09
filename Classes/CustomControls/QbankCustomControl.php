<?php

namespace Pixelant\Qbank\CustomControls;

use Pixelant\Qbank\Utility\QbankUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class QbankCustomControl extends \TYPO3\CMS\Backend\Form\Container\InlineControlContainer
{
    /**
     * @param array $inlineConfiguration
     * @return string
     */
    protected function renderPossibleRecordsSelectorTypeGroupDB(array $inlineConfiguration)
    {
        $selector = parent::renderPossibleRecordsSelectorTypeGroupDB($inlineConfiguration);
        $button = $this->renderQBankButton($inlineConfiguration);

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
    protected function renderQBankButton(array $inlineConfiguration): string
    {
        $languageService = $this->getLanguageService();
        $target_folder = QbankUtility::getSettingTargetFolderIdentifier();
        $resourceFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ResourceFactory::class);
        $folder = $resourceFactory->getFolderObjectFromCombinedIdentifier($target_folder);
        if (
            $folder instanceof Folder
            && $folder->getStorage()->checkUserActionPermission('add', 'File')
        ) {
            $groupFieldConfiguration = $inlineConfiguration['selectorOrUniqueConfiguration']['config'];
            $foreign_table = $inlineConfiguration['foreign_table'];
            $allowed = $groupFieldConfiguration['allowed'];
            $currentStructureDomObjectIdPrefix = $this->inlineStackProcessor->getCurrentStructureDomObjectIdPrefix($this->data['inlineFirstPid']);
            $objectPrefix = $currentStructureDomObjectIdPrefix . '-' . $foreign_table;
            $nameObject = $currentStructureDomObjectIdPrefix;

            // $this->requireJsModules[] = 'TYPO3/CMS/Qbank/Qbank';
            $buttonText = htmlspecialchars(LocalizationUtility::translate('qbank_view.button', 'qbank'));
            $titleText = htmlspecialchars(LocalizationUtility::translate('qbank_view.header', 'qbank'));
            $button = '
                <button type="button" class="btn btn-default t3js-qbank-view-btn qbankFile' . $this->inlineData['config'][$nameObject]['md5'] . '"
                    data-qbank-compact-view-url="' . htmlspecialchars($compactViewUrl) . '"
                    data-target-folder="' . htmlspecialchars($folder->getCombinedIdentifier()) . '"
                    data-title="' . htmlspecialchars($titleText) . '"
                    data-file-irre-object="' . htmlspecialchars($objectPrefix) . '"
                    data-file-allowed="' . htmlspecialchars($allowed) . '"
                    >
                    ' . $this->iconFactory->getIcon('actions-cloud', Icon::SIZE_SMALL)->render() . '
                    ' . $buttonText .
                '</button>';

            return $button;
            $qbankButton .= $this->getAddButton(
                $objectPrefix,
                $folder,
                $onClick
            );
            $qbankButton .= '<div class="form-group">' . $buttons . '</div>';
        } else {
            $errorText = htmlspecialchars(LocalizationUtility::translate('qbank_view.error-no-storage-access', 'qbank'));

            return '&nbsp;<div class="alert alert-danger" style="display: inline-block">
                ' . $this->iconFactory->getIcon('actions-cloud', Icon::SIZE_SMALL)->render() . '
                ' . $errorText . '
                </div>';
        }
    }
}
