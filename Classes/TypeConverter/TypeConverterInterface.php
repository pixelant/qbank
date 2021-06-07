<?php

declare(strict_types=1);

namespace Pixelant\Qbank\TypeConverter;

/**
 * Interface for type convertors (QBank data type to TYPO3 database compatible value).
 */
interface TypeConverterInterface
{
    /**
     * Returns an array of the supported QBank data type IDs.
     *
     * @return array
     */
    public function getSupportedSourceTypes(): array;

    /**
     * Returns true if the converter can convert from the supplied QBank data type ID.
     *
     * @param int $qbankDataTypeId
     * @return bool
     */
    public function canConvertFrom(int $qbankDataTypeId): bool;

    /**
     * Convert $sourceValue.
     *
     * @param $sourceValue
     * @param int $qbankDataTypeId
     * @return mixed
     */
    public function convertFrom($sourceValue, int $qbankDataTypeId);

    /**
     * Returns true if the converter can accept a value as an array.
     *
     * @return bool
     */
    public function acceptsArray(): bool;
}
