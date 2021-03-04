<?php

namespace Pixelant\Qbank\CustomControls;

use Pixelant\Qbank\Utility\QbankUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class QbankCustomControl
{
    public function render($parameters, $pObj)
    {
        $groupFieldConfiguration = $parameters['config']['selectorOrUniqueConfiguration']['config'];
        if (!is_array($groupFieldConfiguration)) {
            $groupFieldConfiguration = $parameters['config']['foreign_selector_fieldTcaOverride']['config'];
        }
        $maxFileSize = GeneralUtility::getMaxUploadFileSize() * 1024;
        $allowed = $groupFieldConfiguration['appearance']['elementBrowserAllowed'];
        $objectPrefix = $parameters['nameObject'] . '-' . $parameters['config']['foreign_table'];
        $nameObjectParts = GeneralUtility::trimExplode('-', $parameters['nameObject']);
        $target_folder = QbankUtility::getSettingTargetFolderIdentifier();

        $browserParams = $target_folder . '|' . $maxFileSize . '|' . $allowed . '|' . $objectPrefix . '|||';
        $onClick = 'setFormValueOpenBrowser(' . GeneralUtility::quoteJSvalue('qbankfile') . ', ' . GeneralUtility::quoteJSvalue($browserParams) . '); return false;';
        $createNewRelationText = QbankUtility::getLabel('ImportQbankMedia', null, true);
        $resourceFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\ResourceFactory::class);
        $folder = $resourceFactory->getFolderObjectFromCombinedIdentifier($target_folder);
        if (
            $folder instanceof Folder
            && $folder->getStorage()->checkUserActionPermission('add', 'File')
        ) {
            $qbankButton .= $this->getAddButton(
                $objectPrefix,
                $folder,
                $onClick
            );
            $qbankButton .= '<div class="form-group">' . $buttons . '</div>';
        }

        return $qbankButton;
    }

    protected function getButtonClass()
    {
        return 'btn btn-default inlineNewQbankRelationButton qbankFile';
    }

    /**
     * Returns the HTML markup for the "Add media from XXX" button.
     * @param string $objectPrefix
     * @param Folder $folder
     * @param string $onClick
     * @return string
     */
    protected function getAddButton(string $objectPrefix, Folder $folder, $onClick)
    {
        $buttonAttributes = [
            'data-file-irre-object' => htmlspecialchars($objectPrefix),
            'data-target-folder' => htmlspecialchars($folder->getCombinedIdentifier()),
        ];
        $buttonAttributes = array_merge($buttonAttributes, self::getAddButtonAttributes());

        $additionalAttributes = '';
        foreach ($buttonAttributes as $property => $value) {
            $additionalAttributes .= sprintf(' %s="%s" ', $property, $value);
        }

        return '<button type="button" onclick="' . htmlspecialchars($onClick) . '" class="' . self::getButtonClass() . '"
                        ' . $additionalAttributes . '
                    >
                    ' . self::getAddButtonIcon() . '
                    ' . LocalizationUtility::translate('button.add_media', 'qbank') .
                '</button>
               ';
    }

    /**
     * Returns the markup for the icon of the "Add media" button.
     * @return string
     */
    public function getAddButtonIcon(): string
    {
        return '<span class="t3js-icon icon icon-size-small icon-state-default icon-actions-online-media-add" data-identifier="actions-shutterstock-media-add">
                <span class="icon-markup">
                    <svg class="icon-color" role="img"><use xlink:href="/typo3/sysext/core/Resources/Public/Icons/T3Icons/sprites/actions.svg#actions-cloud" /></svg>
                </span>
            </span>';
    }

    /**
     * Returns the additional attributes added to the "Add media button", so they can be used in Javascript later.
     * @return array
     */
    public function getAddButtonAttributes(): array
    {
        $buttonLabel = LocalizationUtility::translate('button.add_media', 'qbank');
        $submitButtonLabel = LocalizationUtility::translate('button.submit', 'qbank');
        $cancelLabel = LocalizationUtility::translate('button.cancel', 'qbank');
        $placeholderLabel = LocalizationUtility::translate('placeholder.search', 'qbank');

        return [
            'title' => $buttonLabel,
            'data-btn-submit' => $submitButtonLabel,
            'data-placeholder' => $placeholderLabel,
            'data-btn-cancel' => $cancelLabel,
        ];
    }
}
