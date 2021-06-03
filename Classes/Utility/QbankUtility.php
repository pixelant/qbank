<?php

declare(strict_types=1);

namespace Pixelant\Qbank\Utility;

use Pixelant\Qbank\Configuration\ExtensionConfigurationManager;
use QBNK\QBank\API\Credentials;
use QBNK\QBank\API\QBankApi;
use TYPO3\CMS\Core\Resource\Exception\FolderDoesNotExistException;
use TYPO3\CMS\Core\Resource\Exception\InsufficientFolderAccessPermissionsException;
use TYPO3\CMS\Core\Resource\Exception\InvalidConfigurationException;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Convenience methods for the extension.
 */
class QbankUtility
{
    /**
     * @var QBankApi|null
     */
    protected static $api = null;

    /**
     * Returns the download folder object if it is writeable and accessible for the current backend user. The folder is
     * created if it doesn't exist.
     *
     * @return Folder|null
     * @throws InvalidConfigurationException
     */
    public static function getDownloadFolder(): ?Folder
    {
        /** @var ResourceFactory $resourceFactory */
        $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
        /** @var ExtensionConfigurationManager $extensionConfigurationManager */
        $extensionConfigurationManager = self::getConfigurationManager();

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

    /**
     * Returns an authenticated QBank API object.
     *
     * @return QBankApi
     */
    public static function getApi(): QBankApi
    {
        if (self::$api !== null) {
            return self::$api;
        }

        /** @var ExtensionConfigurationManager $extensionConfigurationManager */
        $extensionConfigurationManager = self::getConfigurationManager();

        $credentials = new Credentials(
            $extensionConfigurationManager->getClientId(),
            $extensionConfigurationManager->getUsername(),
            $extensionConfigurationManager->getPassword()
        );

        self::$api = new QBankApi(
            'https://' . $extensionConfigurationManager->getHost() . '/',
            $credentials
        );

        return self::$api;
    }

    /**
     * Returns an access token, e.g. for JavaScript use.
     *
     * @return string
     */
    public static function getAccessToken(): string
    {
        return self::getApi()->getTokens()['accessToken']->getToken();
    }

    /**
     * Get the configuration manager object.
     *
     * @return ExtensionConfigurationManager
     */
    public static function getConfigurationManager(): ExtensionConfigurationManager
    {
        // @noinspection PhpIncompatibleReturnTypeInspection
        return GeneralUtility::makeInstance(ExtensionConfigurationManager::class);
    }

    /**
     * Converts a QBank RFC3339 date to Unix timestamp.
     *
     * @param string $date
     * @return int
     */
    public static function qbankDateStringToUnixTimestamp(string $date): int
    {
        return self::qbankDateStringToDateTime()->getTimestamp();
    }

    /**
     * Converts a QBank RFC3339 date to a PHP DateTime object.
     *
     * @param string $date
     * @return \DateTime
     */
    public static function qbankDateStringToDateTime(string $date): \DateTime
    {
        $parsedDate = \DateTime::createFromFormat(
            'Y-m-d\TH:i:sP',
            $date
        );

        if ($parsedDate === false) {
            throw new \InvalidArgumentException(
                'The supplied date string "' . $date . '" could not be parsed as a valid QBank date.',
                1622642058
            );
        }

        return $parsedDate;
    }
}
