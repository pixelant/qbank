<?php

declare(strict_types=1);


namespace Pixelant\Qbank\Service\Event;

use Pixelant\Qbank\Domain\Model\Qbank\MediaProperty;

/**
 * Called to fetch all QBank media property types available for synchronization.
 */
class CollectMediaPropertiesEvent
{
    /**
     * @var MediaProperty[]
     */
    protected $properties = [];

    /**
     * @return MediaProperty[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * Add an array of media properties.
     *
     * @param MediaProperty[] $mediaProperties
     */
    public function addMediaProperties(array $mediaProperties)
    {
        $this->properties = array_merge($this->properties, $mediaProperties);
    }

    /**
     * Add a single media property.
     *
     * @param MediaProperty $mediaProperty
     */
    public function addMediaProperty(MediaProperty $mediaProperty)
    {
        $this->addMediaProperties([$mediaProperty]);
    }
}
