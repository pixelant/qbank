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
     * Updated property fields for the sys_file_metadata table. Must be DataHandler compatible.
     *
     * @var array
     */
    protected static $updatedProperties = [];

    /**
     * @inheritDoc
     */
    public function __invoke(FilePropertyChangeEvent $event): void
    {
        if ($event->getFile()->getMetaData()->offsetExists($event->getPropertyName())) {
            self::$updatedProperties[$event->getPropertyName()] = $event->getPropertyValue();
            $event->stopPropagation();
        }
    }

    /**
     * Returns field data.
     *
     * @return array
     */
    public static function getUpdatedProperties(): array
    {
        return self::$updatedProperties;
    }

    /**
     * Reset the updated properties array.
     */
    public static function resetUpdatedProperties(): void
    {
        self::$updatedProperties = [];
    }
}
