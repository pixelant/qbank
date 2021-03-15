<?php

declare(strict_types=1);


namespace Pixelant\Qbank\Service;


use Pixelant\Qbank\Configuration\ExtensionConfigurationManager;
use Pixelant\Qbank\Repository\MediaRepository;
use QBNK\QBank\API\Model\MediaResponse;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class QbankService implements SingletonInterface
{
    /**
     * @var ExtensionConfigurationManager
     */
    protected ExtensionConfigurationManager $configuration;

    /**
     * @var MediaRepository
     */
    protected MediaRepository $mediaRepository;

    /**
     * @var ResourceFactory
     */
    protected ResourceFactory $resourceFactory;

    /**
     * SelectorController constructor.
     * @param ExtensionConfigurationManager $configuration
     * @param MediaRepository $mediaRepository
     * @param ResourceFactory $resourceFactory
     */
    public function __construct(
        ExtensionConfigurationManager $configuration,
        MediaRepository $mediaRepository,
        ResourceFactory $resourceFactory
    )
    {
        $this->configuration = $configuration;
        $this->mediaRepository = $mediaRepository;
        $this->resourceFactory = $resourceFactory;
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
     * Update a sys_file record with QBank timestamp and relation information
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
    )
    {
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
     * Returns a QueryBuilder object for the sys_file table.
     *
     * @return QueryBuilder
     */
    protected function getFileQueryBuilder(): QueryBuilder
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_file');
    }
}
