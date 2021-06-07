<?php

declare(strict_types=1);

namespace Pixelant\Qbank\Utility;

use Pixelant\Qbank\Domain\Model\Qbank\MediaPropertyValue;
use Pixelant\Qbank\Repository\PropertyTypeRepository;
use Pixelant\Qbank\TypeConverter\Exception\InvalidTypeConverterException;
use Pixelant\Qbank\TypeConverter\TypeConverterInterface;
use QBNK\QBank\API\Model\PropertyType;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Convenience methods relating to QBank media and TYPO3 File properties.
 */
class PropertyUtility
{
    protected static $filePropertyTypeConverterCache = [];

    /**
     * Returns the file properties array from $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['qbank']['fileProperties'].
     *
     * @return array
     */
    public static function getFileProperties(): array
    {
        $properties = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['qbank']['fileProperties'] ?? [];

        foreach ($properties as $key => &$property) {
            if (is_callable($property['label'])) {
                $property['label'] = $property['label']() ?? $key;
            }

            if (!isset($property['label'])) {
                $property['label'] = $key;
            }
        }

        return $properties;
    }

    /**
     * Returns the correct type converter for a File object property.
     *
     * @param string $filePropertyName
     * @return TypeConverterInterface
     * @throws InvalidTypeConverterException
     */
    public static function getTypeConverterForFileProperty(string $filePropertyName): TypeConverterInterface
    {
        if (isset(self::$filePropertyTypeConverterCache[$filePropertyName])) {
            return self::$filePropertyTypeConverterCache[$filePropertyName];
        }

        $propertyConfiguration = self::getFileProperties()[$filePropertyName];

        if (!isset($propertyConfiguration['typeConverter'])) {
            throw new InvalidTypeConverterException(
                'No TypeConverter defined for the File object property "' . $filePropertyName . '".',
                1622639980
            );
        }

        // @noinspection PhpIncompatibleReturnTypeInspection
        return GeneralUtility::makeInstance($propertyConfiguration['typeConverter']);
    }

    /**
     * Returns the File object property compatible value from a QBank media property.
     *
     * @param MediaPropertyValue $mediaPropertyValue A QBank property value.
     * @param string $filePropertyName The file property to convert for.
     * @return string|int|float
     * @throws \InvalidArgumentException
     */
    public static function convertQbankToFileProperty(MediaPropertyValue $mediaPropertyValue, string $filePropertyName)
    {
        $typeConverter = self::getTypeConverterForFileProperty($filePropertyName);

        if (is_array($mediaPropertyValue->getValue()) && !$typeConverter->acceptsArray()) {
            throw new \InvalidArgumentException(
                'Array value is not accepted for the TypeConverter "' . get_class($typeConverter) . '".',
                1622652487
            );
        }

        return $typeConverter->convertFrom(
            $mediaPropertyValue->getValue(),
            $mediaPropertyValue->getProperty()->getDataTypeId()
        );
    }

    /**
     * Returns an array of all the enabled File object property keys with TypeConverters configured.
     *
     * @return array
     */
    public static function getEnabledFilePropertiesWithTypeConverters(): array
    {
        $properties = [];

        foreach (self::getFileProperties() as $name => $configuration) {
            if (isset($configuration['typeConverter']) && !$configuration['disabled']) {
                $properties[] = $name;
            }
        }

        return $properties;
    }

    /**
     * Returns the QBank PropertyType ID matching the property with $systemName.
     *
     * @param string $systemName
     * @return int
     */
    public static function getQbankPropertyTypeIdForSystemName(string $systemName): int
    {
        /** @var PropertyType $propertyType */
        $propertyType = GeneralUtility::makeInstance(PropertyTypeRepository::class)->findBySystemName($systemName);

        if ($propertyType !== null) {
            return $propertyType->getDataTypeId();
        }

        return 0;
    }

    /**
     * Returns a label path or label for $filePropertyName.
     *
     * @param string $filePropertyName The file property.
     * @return string
     */
    public static function getLabelForFileProperty(string $filePropertyName): string
    {
        return self::getFileProperties()[$filePropertyName]['label'];
    }
}
