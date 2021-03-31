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
class PageFileReferenceUrlEventHandler implements FileReferenceUrlEventHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(FileReferenceUrlEvent $event): void
    {
        if ($event->getForeignTable() === 'pages') {
            try {
                /** @var Site $site */
                $site = GeneralUtility::makeInstance(SiteFinder::class)
                    ->getSiteByPageId($event->getForeignId());
            } catch (SiteNotFoundException $exception) {
                $event->setUrl('');

                return;
            }

            $event->setUrl(
                (string)$site->getRouter()->generateUri(
                    $event->getForeignId()
                )
            );
        }
    }
}
