<?php

declare(strict_types=1);


namespace Pixelant\Qbank\Service\Event\FilePropertyChangeEventHandler;


use Pixelant\Qbank\Service\Event\FilePropertyChangeEvent;
use Pixelant\Qbank\Service\Event\FilePropertyChangeEventHandler;

/**
 * Handles changes to a File object's file name in the filesystem.
 */
class FileNameFilePropertyChangeEventHandler implements FilePropertyChangeEventHandler
{
    /**
     * @inheritDoc
     */
    public function __invoke(FilePropertyChangeEvent $event): void
    {
        if ($event->getPropertyName() === 'name') {
            $fileName = $event->getPropertyValue();

            // Only rename to a non-empty name and maintain the extension.
            if (trim(pathinfo($fileName, PATHINFO_FILENAME)) !== '') {
                $event->getFile()->rename(
                    trim(pathinfo($fileName, PATHINFO_FILENAME))
                    . '.' . $event->getFile()->getExtension()
                );
            }

            $event->stopPropagation();
        }
    }
}
