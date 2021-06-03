<?php

declare(strict_types=1);


namespace Pixelant\Qbank\TypeConverter;

/**
 * Abstract type converter (QBank data type to TYPO3 database compatible value).
 */
abstract class AbstractTypeConverter implements TypeConverterInterface
{
    /**
     * @var array
     */
    protected const SOURCE_TYPES = [];

    /**
     * @inheritDoc
     */
    public function getSupportedSourceTypes(): array
    {
        return static::SOURCE_TYPES;
    }

    /**
     * @inheritDoc
     */
    public function canConvertFrom(int $qbankDataTypeId): bool
    {
        return in_array($qbankDataTypeId, $this->getSupportedSourceTypes(), true);
    }

    /**
     * @inheritDoc
     */
    public function acceptsArray(): bool
    {
        return true;
    }
}
