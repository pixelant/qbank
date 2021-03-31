<?php

declare(strict_types=1);

namespace Pixelant\Qbank\Service\Event\ResolvePageTitleEventHandler;

use Pixelant\Qbank\Service\Event\ResolvePageTitleEvent;
use Pixelant\Qbank\Service\Event\ResolvePageTitleEventHandlerInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;

/**
 * Default title generator. Should be executed last.
 */
class FileReferenceResolvePageTitleEventHandler implements ResolvePageTitleEventHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(ResolvePageTitleEvent $event): void
    {
        if ($event->getTable() === 'sys_file_reference') {
            $fileReference = BackendUtility::getRecord(
                $event->getTable(),
                $event->getUid()
            );

            if ($fileReference['tablenames'] === 'pages') {
                $event->setPageTitleFromPageId($fileReference['uid_foreign']);

                return;
            }

            $record = BackendUtility::getRecord(
                $fileReference['tablenames'],
                $fileReference['uid_foreign']
            );

            if ($record && $record['pid']) {
                $event->setPageTitleFromPageId($record['pid']);
            }
        }
    }
}
