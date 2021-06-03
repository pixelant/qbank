<?php

declare(strict_types=1);

namespace Pixelant\Qbank\Service;

use Pixelant\Qbank\Configuration\ExtensionConfigurationManager;
use Pixelant\Qbank\Domain\Model\Qbank\MediaProperty;
use Pixelant\Qbank\Domain\Model\Qbank\MediaPropertyValue;
use Pixelant\Qbank\Repository\MappingRepository;
use Pixelant\Qbank\Repository\MediaRepository;
use Pixelant\Qbank\Repository\MediaUsageRepository;
use Pixelant\Qbank\Repository\PropertyTypeRepository;
use Pixelant\Qbank\Service\Event\CollectMediaPropertiesEvent;
use Pixelant\Qbank\Service\Event\ExtractMediaPropertyValuesEvent;
use Pixelant\Qbank\Service\Event\FilePropertyChangeEvent;
use Pixelant\Qbank\Service\Event\FileReferenceUrlEvent;
use Pixelant\Qbank\Service\Event\ResolvePageTitleEvent;
use Pixelant\Qbank\Utility\PropertyUtility;
use QBNK\QBank\API\Model\MediaUsage;
use QBNK\QBank\API\Model\PropertyType;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class QbankService implements SingletonInterface
{
    /**
     * @var ExtensionConfigurationManager
     */
    protected $configuration;

    /**
     * @var MediaRepository
     */
    protected $mediaRepository;

    /**
     * @var ResourceFactory
     */
    protected $resourceFactory;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var array|null
     */
    private $mediaPropertiesCache;

    /**
     * SelectorController constructor.
     * @param ExtensionConfigurationManager $configuration
     * @param MediaRepository $mediaRepository
     * @param ResourceFactory $resourceFactory
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct(
        ExtensionConfigurationManager $configuration,
        MediaRepository $mediaRepository,
        ResourceFactory $resourceFactory,
        EventDispatcher $eventDispatcher
    ) {
        $this->configuration = $configuration;
        $this->mediaRepository = $mediaRepository;
        $this->resourceFactory = $resourceFactory;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Downloads a media file if it doesn't already exist. Returns the local File object.
     *
     * @param int $id
     * @return File The local file representation
     * @throws \TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException
     */
    public function createLocalMediaCopy(int $id): ?File
    {
        $file = $this->findLocalMediaCopy($id);

        if ($file !== null) {
            return $file;
        }

        $downloadFolder = $this->resourceFactory->getFolderObjectFromCombinedIdentifier(
            $this->configuration->getDownloadFolder()
        );

        $media = $this->mediaRepository->findById($id);

        $fileResource = $this->mediaRepository->downloadById($id, $downloadFolder);

        $file = $downloadFolder->createFile($media->getFilename());
        $file->setContents(fread($fileResource, $media->getSize()));

        $this->synchronizeMetadata($file->getUid());

        $this->updateFileRecord($file->getUid(), true, false, $id);

        return $file;
    }

    /**
     * Returns a File object equivalent of a media item. Or null if it doesn't exist.
     *
     * @param int $id
     * @return File|null
     * @throws \TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException
     */
    protected function findLocalMediaCopy(int $id): ?File
    {
        $queryBuilder = $this->getFileQueryBuilder();

        $fileUid = $queryBuilder
            ->select('uid')
            ->from('sys_file')
            ->where($queryBuilder->expr()->eq(
                'tx_qbank_id',
                $queryBuilder->createNamedParameter($id, \PDO::PARAM_INT)
            ))
            ->execute()
            ->fetchColumn(0);

        if ($fileUid === false) {
            return null;
        }

        return $this->resourceFactory->getFileObject($fileUid);
    }

    /**
     * Returns a QueryBuilder object for the sys_file table.
     *
     * @return QueryBuilder
     */
    protected function getFileQueryBuilder(): QueryBuilder
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_file');
    }

    /**
     * Update a sys_file record with QBank timestamp and relation information.
     *
     * @param int $fileUid The local file UID
     * @param bool $changedFile True if file has been changed
     * @param bool $changedMetadata True if metadata has been changed
     * @param int $qbankId The QBank media ID. Not needed unless it's the first time record is written.
     */
    protected function updateFileRecord(
        int $fileUid,
        bool $changedFile,
        bool $changedMetadata,
        int $qbankId = 0
    ): void {
        $queryBuilder = $this->getFileQueryBuilder();
        $queryBuilder->update('sys_file');

        if ($changedFile) {
            $queryBuilder->set(
                'tx_qbank_file_timestamp',
                time()
            );
        }

        if ($changedMetadata) {
            $queryBuilder->set(
                'tx_qbank_metadata_timestamp',
                time()
            );
        }

        if ($qbankId > 0) {
            $queryBuilder->set(
                'tx_qbank_id',
                $qbankId
            );
        }

        $queryBuilder
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($fileUid, \PDO::PARAM_INT)))
            ->execute();
    }

    /**
     * Remove a media usage from a file reference.
     *
     * @param int $fileReferenceId
     */
    public function removeMediaUsageInFileReference(int $fileReferenceId): void
    {
        /** @var FileReference $fileReference */
        $fileReference = GeneralUtility::makeInstance(ResourceFactory::class)->getFileReferenceObject($fileReferenceId);

        /** @var File $file */
        $file = $fileReference->getOriginalFile();

        if (!$this->isQbankFile($file->getUid())) {
            return;
        }

        $this->removeMediaUsage(
            $file->getUid(),
            'sys_file_reference',
            $fileReferenceId
        );
    }

    /**
     * Synchronize metadata for a particular file UID.
     *
     * @param int $fileId The FAL file UID
     * @throws \TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException
     */
    public function synchronizeMetadata(int $fileId)
    {
        $qbankId = $this->getQbankMediaIdentifierForFile($fileId);

        if ($qbankId === 0) {
            return;
        }

        $qbankRecord = $this->mediaRepository->findById($qbankId);

        $metaDataMappings = GeneralUtility::makeInstance(MappingRepository::class)->findAllAsKeyValuePairs();

        $qbankPropertyValues = $this
            ->eventDispatcher
            ->dispatch(new ExtractMediaPropertyValuesEvent($qbankRecord))
            ->getValues();

        $file = $this->findLocalMediaCopy($qbankId);

        /** @var MediaPropertyValue $qbankPropertyValue */
        foreach ($qbankPropertyValues as $qbankPropertyValue) {
            $sourceProperty = $qbankPropertyValue->getProperty()->getKey();
            $targetProperty = $metaDataMappings[$sourceProperty];

            if (!isset($targetProperty)) {
                continue;
            }

            $this->eventDispatcher->dispatch(new FilePropertyChangeEvent(
                $file,
                $targetProperty,
                PropertyUtility::convertQbankToFileProperty(
                    $qbankPropertyValue,
                    $targetProperty
                )
            ));
        }

        $this->updateFileRecord($file->getUid(), false, true, $qbankId);

        // Should be put in its own event.
        $file->getMetaData()->save();
    }

    /**
     * Returns true if the sys_file uid suppplied in $fileId is a QBank file.
     *
     * @param int $fileId
     */
    protected function isQbankFile(int $fileId): bool
    {
        return (bool)$this->getQbankMediaIdentifierForFile($fileId);
    }

    /**
     * Returns the QBank media ID for the sys_file UID supplied in $fileId.
     *
     * @param int $fileId
     * @return int The QBank media ID. Zero if not found or file is not QBank media.
     */
    protected function getQbankMediaIdentifierForFile(int $fileId): int
    {
        return (int)(BackendUtility::getRecord(
            'sys_file',
            $fileId,
            'tx_qbank_id'
        ) ?? [
            'tx_qbank_id' => 0,
        ])['tx_qbank_id'];
    }

    /**
     * Remove media usage in a specific record.
     *
     * @param int $fileId
     * @param string $table
     * @param int $recordUid
     */
    protected function removeMediaUsage(int $fileId, string $table, int $recordUid): void
    {
        /** @var MediaUsageRepository $usageRepository */
        $usageRepository = GeneralUtility::makeInstance(MediaUsageRepository::class);

        $usageRepository->removeOneByQbankAndLocalId(
            $this->getQbankMediaIdentifierForFile($fileId),
            $table . '_' . $recordUid
        );
    }

    /**
     * Report media usage in table sys_file_reference to QBank.
     *
     * @param int $fileReferenceId A sys_file_reference record UID
     */
    public function reportMediaUsageInFileReference(int $fileReferenceId): void
    {
        /** @var FileReference $fileReference */
        $fileReference = GeneralUtility::makeInstance(ResourceFactory::class)->getFileReferenceObject($fileReferenceId);

        /** @var File $file */
        $file = $fileReference->getOriginalFile();

        if (!$this->isQbankFile($file->getUid())) {
            return;
        }

        $url = $this->eventDispatcher->dispatch(
            new FileReferenceUrlEvent($fileReference, $this)
        )->getUrl() ?? '';

        // No FileReferenceUrlEvent generated usage for file.
        if ($url === '') {
            return;
        }

        $this->reportMediaUsage(
            $file->getUid(),
            $url,
            'sys_file_reference',
            $fileReferenceId
        );
    }

    /**
     * Report media usage to QBank.
     *
     * @param int $fileId
     * @param string $url
     * @param string $table
     * @param int $recordUid
     */
    protected function reportMediaUsage(int $fileId, string $url, string $table, int $recordUid): void
    {
        /** @var File $file */
        $file = GeneralUtility::makeInstance(ResourceFactory::class)->getFileObject($fileId);

        $metaData = $file->getMetaData();

        $cropping = '';
        if ($table === 'sys_file_reference') {
            $cropping = GeneralUtility::makeInstance(ResourceFactory::class)
                ->getFileReferenceObject($recordUid)
                ->getReferenceProperty('crop') ?? '';
        }

        $title = $this->eventDispatcher->dispatch(
            new ResolvePageTitleEvent($table, $recordUid, $this)
        )->getTitle() ?? '';

        $createdByName = $GLOBALS['BE_USER']->user['username'];
        if ($GLOBALS['BE_USER']->user['realName'] !== '') {
            $createdByName = $GLOBALS['BE_USER']->user['realName'] . ' (' . $GLOBALS['BE_USER']->user['username'] . ')';
        }

        $createdByEmail = $GLOBALS['BE_USER']->user['email'];

        $context = [
            'localID' => $table . '_' . $recordUid,
            'cropCoords' => $cropping,
            'pageTitle' => $title,
            'createdByName' => $createdByName,
            'createdByEmail' => $createdByEmail,
        ];

        $language = $this->getLanguageForRecord($table, $recordUid);

        $mediaUsage = new MediaUsage();
        $mediaUsage->setMediaId($this->getQbankMediaIdentifierForFile($fileId));
        $mediaUsage->setMediaUrl($file->getPublicUrl());
        $mediaUsage->setPageUrl($url);
        $mediaUsage->setContext($context);
        $mediaUsage->setLanguage($language);

        /** @var MediaUsageRepository $usageRepository */
        $usageRepository = GeneralUtility::makeInstance(MediaUsageRepository::class);
        $usageRepository->add($mediaUsage);
    }

    /**
     * Returns the language string for a database record.
     *
     * @param $table
     * @param $uid
     * @return string
     */
    protected function getLanguageForRecord($table, $uid): string
    {
        $record = BackendUtility::getRecord($table, $uid);
        $pageId = $record['pid'];

        if (!BackendUtility::isTableLocalizable($table)) {
            if ($record === null || !isset($record['pid'])) {
                return '';
            }

            $record = BackendUtility::getRecord('pages', $record['pid']);
            $pageId = $record['pid'];
        }

        $languageField = $GLOBALS['TCA'][$table]['ctrl']['languageField'];

        try {
            $site = GeneralUtility::makeInstance(SiteFinder::class)
                ->getSiteByPageId($pageId);
        } catch (SiteNotFoundException $exception) {
            return '';
        }

        $language = $site->getLanguageById($record[$languageField]);

        if (!$language) {
            return '';
        }

        return $language->getTwoLetterIsoCode();
    }

    /**
     * List all propertysets defined in QBank.
     *
     * @return MediaProperty[]
     */
    public function fetchMediaProperties(): array
    {
        if (isset($this->mediaPropertiesCache)) {
            return $this->mediaPropertiesCache;
        }

        $this->mediaPropertiesCache = [];

        /** @var MediaProperty $property */
        foreach ($this->eventDispatcher->dispatch(new CollectMediaPropertiesEvent())->getProperties() as $property) {
            $this->mediaPropertiesCache[$property->getKey()] = $property;
        }

        return $this->mediaPropertiesCache;
    }

    /**
     * List all propertysets defined in QBank.
     *
     * @return PropertyType|null
     * @param mixed $systemName
     */
    public function fetchPropertyTypeBySystemName($systemName)
    {
        /** @var PropertyTypeRepository $propertyTypeRepository */
        $propertyTypeRepository = GeneralUtility::makeInstance(PropertyTypeRepository::class);

        return $propertyTypeRepository->findBySystemName($systemName);
    }
}
