<?php

declare(strict_types=1);

namespace Pixelant\Qbank\TypeConverter;

use Pixelant\Qbank\Utility\QbankUtility;
use QBNK\QBank\API\Model\PropertyType;

/**
 * Converts QBank media properties to integer.
 */
class IntegerTypeConverter extends AbstractTypeConverter
{
    protected const SOURCE_TYPES = [
        PropertyType::DATATYPE_BOOLEAN,
        PropertyType::DATATYPE_DATETIME,
        PropertyType::DATATYPE_DECIMAL,
        PropertyType::DATATYPE_FLOAT,
        PropertyType::DATATYPE_INTEGER,
        PropertyType::DATATYPE_STRING,
    ];

    /**
     * {@inheritdoc}
     */
    public function convertFrom($sourceValue, int $qbankDataTypeId)
    {
        if ($qbankDataTypeId === PropertyType::DATATYPE_DATETIME && $sourceValue instanceof \DateTime) {
            return $sourceValue->getTimestamp();
        }
        if ($qbankDataTypeId === PropertyType::DATATYPE_DATETIME && is_string($sourceValue)) {
            return QbankUtility::qbankDateStringToUnixTimestamp($sourceValue);
        }

        return (int)$sourceValue;
    }

    /**
     * {@inheritdoc}
     */
    public function acceptsArray(): bool
    {
        return false;
    }
}
