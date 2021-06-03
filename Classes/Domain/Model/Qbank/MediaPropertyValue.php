<?php

declare(strict_types=1);


namespace Pixelant\Qbank\Domain\Model\Qbank;

/**
 * Representation of a QBank Property value. Can be any value.
 */
class MediaPropertyValue
{
    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var MediaProperty
     */
    protected $property;

    /**
     * PropertyValue constructor.
     * @param $value
     * @param int $dataTypeId
     * @param string $propertyName
     */
    public function __construct($value, MediaProperty $property)
    {
        $this->value = $value;
        $this->property = $property;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return MediaProperty
     */
    public function getProperty(): MediaProperty
    {
        return $this->property;
    }

    /**
     * Returns true if the value is an array of values.
     *
     * @return bool
     */
    public function hasArrayValue(): bool
    {
        return is_array($this->value);
    }
}
