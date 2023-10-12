<?php

declare(strict_types=1);

namespace Pixelant\Qbank\Service\Event;

use Pixelant\Qbank\Service\QbankService;
use Psr\EventDispatcher\StoppableEventInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;

/**
 * Event for retrieving a page title.
 */
class ResolvePageTitleEvent implements StoppableEventInterface
{
    /**
     * @var string|null
     */
    protected $title;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var int
     */
    protected $uid;

    /**
     * @var QbankService
     */
    protected $qbankService;

    /**
     * FileReferenceMediaUsageLocationUrlEvent constructor.
     * @param string $table
     * @param int $uid
     * @param QbankService $qbankService
     */
    public function __construct(string $table, int $uid, QbankService $qbankService)
    {
        $this->table = $table;
        $this->uid = $uid;
        $this->qbankService = $qbankService;
    }

    /**
     * {@inheritdoc}
     */
    public function isPropagationStopped(): bool
    {
        return $this->getTitle() !== null;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @return int
     */
    public function getUid(): int
    {
        return $this->uid;
    }

    /**
     * @return QbankService
     */
    public function getQbankService(): QbankService
    {
        return $this->qbankService;
    }

    /**
     * Sets the page title based on a page ID.
     *
     * @param int $pageId
     */
    public function setPageTitleFromPageId(int $pageId): void
    {
        if ($pageId === 0) {
            $this->setTitle('');

            return;
        }

        $this->setTitle(
            BackendUtility::getRecordTitle(
                'pages',
                BackendUtility::getRecord(
                    'pages',
                    $pageId
                )
            )
        );
    }
}
