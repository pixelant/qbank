<?php

declare(strict_types=1);


namespace Pixelant\Qbank\Service\Event\FileReferenceUrlEventHandler;


use Pixelant\Qbank\Service\Event\FileReferenceUrlEvent;
use Pixelant\Qbank\Service\Event\FileReferenceUrlEventHandlerInterface;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Sets the URL to a content element on a page if the event's $fileReference is to a content element.
 */
class ContentElementFileReferenceUrlEventHandler implements FileReferenceUrlEventHandlerInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(FileReferenceUrlEvent $event): void
    {
        if ($event->getForeignTable() === 'tt_content') {
            try {
                /** @var Site $site */
                $site = GeneralUtility::makeInstance(SiteFinder::class)
                    ->getSiteByPageId($event->getForeignRecordPageId());
            } catch (SiteNotFoundException $exception) {
                $event->setUrl('');

                return;
            }

            $event->setUrl(
                (string)$site->getRouter()->generateUri(
                    $event->getForeignRecordPageId(),
                    [],
                    'c' . $event->getForeignId()
                )
            );
        }
    }
}
