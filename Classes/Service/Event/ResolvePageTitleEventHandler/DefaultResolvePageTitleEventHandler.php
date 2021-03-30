<?php

declare(strict_types=1);

namespace Pixelant\Qbank\Service\Event\ResolvePageTitleEventHandler;

use Pixelant\Qbank\Service\Event\ResolvePageTitleEvent;
use Pixelant\Qbank\Service\Event\ResolvePageTitleEventHandlerInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;

/**
 * Default title generator. Should be executed last.
 */
class DefaultResolvePageTitleEventHandler implements ResolvePageTitleEventHandlerInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ResolvePageTitleEvent $event): void
    {
        $pageId = $event->getUid();
        if ($event->getTable() !== 'pages') {
            $pageId = (
                BackendUtility::getRecord(
                    $event->getTable(),
                    $event->getUid(),
                    'pid'
                ) ?? ['pid' => 0]
            )['pid'];
        }

        $event->setPageTitleFromPageId($pageId);
    }
}
