<?php

declare(strict_types=1);


namespace Pixelant\Qbank\Utility;


use Pixelant\Qbank\Configuration\ExtensionConfigurationManager;
use TYPO3\CMS\Core\Resource\Exception;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Convenience methods for the extension.
 */
class QbankUtility
{
    /**
     * Returns the download target folder object if it is writeable and accessible for the current backend user.
     *
     * @return Folder|null
     */
    public static function getDownloadFolder(): ?Folder
    {
        /** @var ResourceFactory $resourceFactory */
        $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
        /** @var ExtensionConfigurationManager $extensionConfigurationManager */
        $extensionConfigurationManager = GeneralUtility::makeInstance(ExtensionConfigurationManager::class);

        try {
            $targetFolder = $resourceFactory->getFolderObjectFromCombinedIdentifier(
                $extensionConfigurationManager->getDownloadFolder()
            );
        } catch (Exception $exception) {
            return null;
        }

        if (
            !$targetFolder->checkActionPermission('write')
            || !$targetFolder->getStorage()->checkUserActionPermission('add', 'File')
        ) {
            return null;
        }

        return $targetFolder;
    }
}
