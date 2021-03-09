<?php

declare(strict_types=1);


namespace Pixelant\Qbank\Utility;


use Pixelant\Qbank\Configuration\ExtensionConfigurationManager;
use QBNK\QBank\API\Credentials;
use QBNK\QBank\API\QBankApi;
use TYPO3\CMS\Core\Resource\Exception\FolderDoesNotExistException;
use TYPO3\CMS\Core\Resource\Exception\InsufficientFolderAccessPermissionsException;
use TYPO3\CMS\Core\Resource\Exception\InvalidConfigurationException;
use TYPO3\CMS\Core\Resource\Exception\InvalidFolderException;
use TYPO3\CMS\Core\Resource\Exception\InvalidTargetFolderException;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Convenience methods for the extension.
 */
class QbankUtility
{
    /**
     * Returns the download folder object if it is writeable and accessible for the current backend user. The folder is
     * created if it doesn't exist.
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
            $downloadFolderPath = $extensionConfigurationManager->getDownloadFolder();
            $downloadFolder = $resourceFactory->getFolderObjectFromCombinedIdentifier($downloadFolderPath);
        } catch (FolderDoesNotExistException $exception) {
            $parts = GeneralUtility::trimExplode(':', $downloadFolderPath);

            if (count($parts) !== 2 || (int)$parts[0] === 0) {
                throw new InvalidConfigurationException(
                    'The download folder "' . $downloadFolderPath . '" cannot be created because it has the wrong ' .
                    'format or because the storage is zero.'
                );
            }

            $storageUid = (int)$parts[0];
            $folderIdentifier = $parts[1];
            $downloadFolder = $resourceFactory->getStorageObject($storageUid)->createFolder($folderIdentifier);
        } catch (InsufficientFolderAccessPermissionsException $exception) {
            return null;
        }

        if (
            !$downloadFolder->checkActionPermission('write')
            || !$downloadFolder->getStorage()->checkUserActionPermission('add', 'File')
        ) {
            return null;
        }

        return $downloadFolder;
    }

    public static function getAccessToken(): string
    {
        /** @var ExtensionConfigurationManager $extensionConfigurationManager */
        $extensionConfigurationManager = GeneralUtility::makeInstance(ExtensionConfigurationManager::class);

        $credentials = new Credentials(
            $extensionConfigurationManager->getClientId(),
            $extensionConfigurationManager->getUsername(),
            $extensionConfigurationManager->getPassword()
        );

        $qbank = new QBankApi(
            'https://' .  $extensionConfigurationManager->getHost() . '/',
            $credentials
        );

        return $qbank->getTokens()['accessToken']->getToken();
    }
}
