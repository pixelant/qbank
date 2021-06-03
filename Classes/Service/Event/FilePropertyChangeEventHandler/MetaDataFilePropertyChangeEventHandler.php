<?php

declare(strict_types=1);


namespace Pixelant\Qbank\Service\Event\FilePropertyChangeEventHandler;


use Pixelant\Qbank\Service\Event\FilePropertyChangeEvent;
use Pixelant\Qbank\Service\Event\FilePropertyChangeEventHandler;

/**
 * Handles changes to fields in a File object's metadata.
 */
class MetaDataFilePropertyChangeEventHandler implements FilePropertyChangeEventHandler
{
    /**
     * @inheritDoc
     */
    public function __invoke(FilePropertyChangeEvent $event): void
    {
        if ($event->getFile()->getMetaData()->offsetExists($event->getPropertyName())) {
            $event->getFile()->getMetaData()->offsetSet($event->getPropertyName(), $event->getPropertyValue());
            $event->stopPropagation();
        }
    }
}
