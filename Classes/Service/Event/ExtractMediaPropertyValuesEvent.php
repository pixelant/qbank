<?php

declare(strict_types=1);


namespace Pixelant\Qbank\Service\Event;

use Pixelant\Qbank\Domain\Model\Qbank\MediaPropertyValue;
use QBNK\QBank\API\Model\MediaResponse;

/**
 * Extract values from a QBank media record.
 */
class ExtractMediaPropertyValuesEvent
{
    /**
     * @var MediaResponse
     */
    protected $mediaResponse;

    /**
     * @var MediaPropertyValue[]
     */
    protected $values = [];

    public function __construct(MediaResponse $mediaResponse)
    {
        $this->mediaResponse = $mediaResponse;
    }

    /**
     * @return MediaResponse
     */
    public function getMediaResponse(): MediaResponse
    {
        return $this->mediaResponse;
    }

    /**
     * @return MediaPropertyValue[]
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * Add a single value
     *
     * @param MediaPropertyValue $value
     */
    public function addValue(MediaPropertyValue $value)
    {
        $this->values[] = $value;
    }

    /**
     * Add multiple media property values.
     *
     * @param MediaPropertyValue[] $values
     */
    public function addValues(array $values)
    {
        $this->values = array_merge($this->values, $values);
    }
}
