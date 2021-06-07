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
     * {@inheritdoc}
     */
    public function getSupportedSourceTypes(): array
    {
        return static::SOURCE_TYPES;
    }

    /**
     * {@inheritdoc}
     */
    public function canConvertFrom(int $qbankDataTypeId): bool
    {
        return in_array($qbankDataTypeId, $this->getSupportedSourceTypes(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function acceptsArray(): bool
    {
        return true;
    }
}
