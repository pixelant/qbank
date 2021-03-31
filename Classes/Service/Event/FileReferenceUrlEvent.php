<?php

declare(strict_types=1);

namespace Pixelant\Qbank\Service\Event;

use Pixelant\Qbank\Service\QbankService;
use Psr\EventDispatcher\StoppableEventInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FileReferenceUrlEvent implements StoppableEventInterface
{
    /**
     * @var string|null
     */
    protected $url = null;

    /**
     * @var FileReference
     */
    protected $fileReference;

    /**
     * @var QbankService
     */
    protected $qbankService;

    /**
     * ProvideMediaUsageLocationUrlEvent constructor.
     *
     * @
     */
    public function __construct(FileReference $fileReference, QbankService $qbankService)
    {
        // If the FileReference instance is too new, the properties haven't been updated, so we need to create one for
        // our own uses.
        if ($fileReference->getReferenceProperty('tablenames') === '') {
            $fileReference = GeneralUtility::makeInstance(
                FileReference::class,
                BackendUtility::getRecord(
                    'sys_file_reference',
                    $fileReference->getUid()
                ),
                GeneralUtility::makeInstance(ResourceFactory::class)
            );
        }

        $this->fileReference = $fileReference;
        $this->qbankService = $qbankService;
    }

    /**
     * {@inheritdoc}
     */
    public function isPropagationStopped(): bool
    {
        return $this->getUrl() !== null;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return FileReference
     */
    public function getFileReference(): FileReference
    {
        return $this->fileReference;
    }

    /**
     * @return QbankService
     */
    public function getQbankService(): QbankService
    {
        return $this->qbankService;
    }

    /**
     * Returns the foreign table name of the FileReference.
     *
     * @return string
     */
    public function getForeignTable(): string
    {
        return $this->getFileReference()->getReferenceProperty('tablenames');
    }

    /**
     * Returns the foreign table uid of the FileReference.
     *
     * @return int
     */
    public function getForeignId(): int
    {
        return $this->getFileReference()->getReferenceProperty('uid_foreign');
    }

    /**
     * Returns the Page ID (PID) of the foreign record.
     *
     * @return int
     */
    public function getForeignRecordPageId(): int
    {
        return BackendUtility::getRecord(
            $this->getForeignTable(),
            $this->getForeignId(),
            'pid'
        )['pid'] ?? ['pid' => 0];
    }
}
