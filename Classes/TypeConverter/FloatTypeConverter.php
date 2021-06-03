<?php
declare(strict_types=1);

namespace Pixelant\Qbank\TypeConverter;

use QBNK\QBank\API\Model\PropertyType;

/**
 *
 * Converts QBank media properties to float.
 */
class FloatTypeConverter extends AbstractTypeConverter
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
     * @inheritDoc
     */
    public function convertFrom($sourceValue, int $qbankDataTypeId)
    {
        return (int)$sourceValue;
    }

    /**
     * @inheritDoc
     */
    public function acceptsArray(): bool
    {
        return false;
    }
}
