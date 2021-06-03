<?php
declare(strict_types=1);

namespace Pixelant\Qbank\Domain\Model\Qbank;

/**
 * A representation of a property within a Qbank media. Can be any property.
 */
class MediaProperty
{
    /**
     * A prefix added to the key to keep properties from different sources apart.
     */
    public const ORIGIN_PREFIX = null;

    /**
     * @var int
     */
    protected $dataTypeId;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $label;

    public function __construct(int $dataTypeId, string $key, string $label)
    {
        $this->dataTypeId = $dataTypeId;
        $this->key = $key;
        $this->label = $label;
    }

    /**
     * @return int
     */
    public function getDataTypeId(): int
    {
        return $this->dataTypeId;
    }

    /**
     * Returns the property key we use as reference in the extension. E.g. "prefix_key"
     *
     * @return string
     */
    public function getKey(): string
    {
        if (static::ORIGIN_PREFIX !== null) {
            return static::ORIGIN_PREFIX . '_' . $this->key;
        }

        return $this->key;
    }

    /**
     * Returns the key without prefix. If getKey() returns "prefix_key", this function returns only "key".
     *
     * @return string
     */
    public function getKeyWithoutPrefix(): string
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }
}
