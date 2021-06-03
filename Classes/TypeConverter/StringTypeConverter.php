<?php
declare(strict_types=1);

namespace Pixelant\Qbank\TypeConverter;

use QBNK\QBank\API\Model\PropertyType;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Converts QBank media properties to string.
 */
class StringTypeConverter extends AbstractTypeConverter
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
        if (is_array($sourceValue)) {
            return implode(',', $sourceValue);
        }

        if ($qbankDataTypeId === PropertyType::DATATYPE_DATETIME && $sourceValue instanceof \DateTime) {
            return $sourceValue->format(
                $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] . ' ' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm']
            );
        }

        return (string)$sourceValue;
    }
}
