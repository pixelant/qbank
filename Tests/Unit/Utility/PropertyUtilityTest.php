<?php

declare(strict_types=1);

namespace Pixelant\Qbank\Unit\Utility;

use Pixelant\Qbank\TypeConverter\StringTypeConverter;
use Pixelant\Qbank\TypeConverter\FloatTypeConverter;
use Pixelant\Qbank\TypeConverter\IntegerTypeConverter;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use Pixelant\Qbank\Domain\Model\Qbank\BaseMediaProperty;
use Pixelant\Qbank\Domain\Model\Qbank\MediaPropertyValue;
use Pixelant\Qbank\Domain\Model\Qbank\PropertyMediaProperty;
use Pixelant\Qbank\Exception\MissingFilePropertyException;
use Pixelant\Qbank\Repository\PropertyTypeRepository;
use Pixelant\Qbank\TypeConverter\Exception\InvalidTypeConverterException;
use Pixelant\Qbank\TypeConverter\TypeConverterInterface;
use Pixelant\Qbank\Utility\PropertyUtility;
use QBNK\QBank\API\Model\PropertyType;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case.
 *
 * @author Pixelant <info@pixelant.net>
 */
class PropertyUtilityTest extends UnitTestCase
{
    protected $resetSingletonInstances = true;

    /**
     * Set up.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['qbank']['fileProperties'] = [
            'name' => [
                'typeConverter' => StringTypeConverter::class,
                'label' => 'Name',
                'disabled' => false,
            ],
            'title' => [
                'typeConverter' => StringTypeConverter::class,
                'label' => 'Title',
                'disabled' => false,
            ],
            'keywords' => [
                'typeConverter' => StringTypeConverter::class,
                'label' => 'JKeywords',
                'disabled' => false,
            ],
            'latitude' => [
                'typeConverter' => FloatTypeConverter::class,
                'label' => 'Latitude',
                'disabled' => false,
            ],
            'content_creation_date' => [
                'typeConverter' => IntegerTypeConverter::class,
                'label' => 'Content Creation Date',
                'disabled' => false,
            ],
            'hidden' => [
                'typeConverter' => IntegerTypeConverter::class,
                'label' => 'Hidden',
                'disabled' => false,
            ],
            'note' => [
                'typeConverter' => StringTypeConverter::class,
                'label' => 'Note',
                'disabled' => true,
            ],
        ];
    }

    /**
     * @test
     */
    public function getFilePropertiesReturnsFileProperties(): void
    {
        self::assertSame(
            PropertyUtility::getFileProperties(),
            $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['qbank']['fileProperties']
        );
    }

    /**
     * @test
     */
    public function getTypeConverterForFilePropertyReturnsTypeConverterInterface(): void
    {
        self::assertInstanceOf(
            TypeConverterInterface::class,
            PropertyUtility::getTypeConverterForFileProperty('name')
        );
    }

    /**
     * @test
     */
    public function getTypeConverterForUnknownFilePropertyThrowsException(): void
    {
        self::expectException(InvalidTypeConverterException::class);

        PropertyUtility::getTypeConverterForFileProperty('unkownProperty');
    }

    /**
     * @test
     */
    public function convertQbankToFilePropertyReturnsFileObjectPropertyCompatibleValue(): void
    {
        // DATATYPE_STRING => string
        $property = new BaseMediaProperty(
            PropertyType::DATATYPE_STRING,
            'name',
            'Name'
        );
        $value = 'A string value';
        $propertyValue = new MediaPropertyValue($value, $property);

        self::assertSame(
            PropertyUtility::convertQbankToFileProperty(
                $propertyValue,
                'name'
            ),
            $value
        );

        // DATATYPE_STRING => Array
        $property = new BaseMediaProperty(
            PropertyType::DATATYPE_STRING,
            'name',
            'Name'
        );
        $value = ['A', 'B', 'C', 'D', 'E'];
        $propertyValue = new MediaPropertyValue($value, $property);

        self::assertSame(
            PropertyUtility::convertQbankToFileProperty(
                $propertyValue,
                'name'
            ),
            implode(',', $value)
        );

        // DATATYPE_DATETIME => integer
        $property = new PropertyMediaProperty(
            PropertyType::DATATYPE_DATETIME,
            'datatypedatetime',
            'DataType DateTime'
        );
        $value = new \DateTime();
        $propertyValue = new MediaPropertyValue($value, $property);

        self::assertSame(
            PropertyUtility::convertQbankToFileProperty(
                $propertyValue,
                'content_creation_date'
            ),
            $value->getTimestamp()
        );

        // DATATYPE_BOOLEAN => integer
        $property = new PropertyMediaProperty(
            PropertyType::DATATYPE_BOOLEAN,
            'datatypeboolean',
            'DataType Boolean'
        );

        $value = true;
        $propertyValue = new MediaPropertyValue($value, $property);

        self::assertSame(
            PropertyUtility::convertQbankToFileProperty(
                $propertyValue,
                'hidden'
            ),
            1
        );

        $value = false;
        $propertyValue = new MediaPropertyValue($value, $property);

        self::assertSame(
            PropertyUtility::convertQbankToFileProperty(
                $propertyValue,
                'hidden'
            ),
            0
        );

        // DATATYPE_INTEGER => integer
        $property = new PropertyMediaProperty(
            PropertyType::DATATYPE_INTEGER,
            'datatypeinteger',
            'DataType Integer'
        );
        $value = 123456;
        $propertyValue = new MediaPropertyValue($value, $property);

        self::assertSame(
            PropertyUtility::convertQbankToFileProperty(
                $propertyValue,
                'content_creation_date'
            ),
            $value
        );

        // DATATYPE_DECIMAL => float
        $property = new PropertyMediaProperty(
            PropertyType::DATATYPE_DECIMAL,
            'datatypedecimal',
            'DataType Decimal'
        );
        $value = 1234.56;
        $propertyValue = new MediaPropertyValue($value, $property);

        self::assertSame(
            PropertyUtility::convertQbankToFileProperty(
                $propertyValue,
                'latitude'
            ),
            $value
        );

        // DATATYPE_FLOAT => float
        $property = new PropertyMediaProperty(
            PropertyType::DATATYPE_FLOAT,
            'datatypefloat',
            'DataType Float'
        );
        $value = 54321.54321;
        $propertyValue = new MediaPropertyValue($value, $property);

        self::assertSame(
            PropertyUtility::convertQbankToFileProperty(
                $propertyValue,
                'latitude'
            ),
            $value
        );
    }

    /**
     * @test
     */
    public function convertQbankToFilePropertyThrowsExceptionIfValueIsArrayAndConverterDoesNotAcceptArray(): void
    {
        // DATATYPE_STRING
        $property = new BaseMediaProperty(
            PropertyType::DATATYPE_STRING,
            'name',
            'Name'
        );
        $value = ['A', 'B', 'C', 'D', 'E'];
        $propertyValue = new MediaPropertyValue($value, $property);

        self::expectException(\InvalidArgumentException::class);

        PropertyUtility::convertQbankToFileProperty(
            $propertyValue,
            'hidden'
        );
    }

    /**
     * @test
     */
    public function getEnabledFilePropertiesWithTypeConvertersDontReturnDisabledProperties(): void
    {
        $fileProperties = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['qbank']['fileProperties'];
        // unset note, it is set as disabled
        unset($fileProperties['note']);

        self::assertSame(
            PropertyUtility::getEnabledFilePropertiesWithTypeConverters(),
            array_keys($fileProperties)
        );
    }

    /**
     * @test
     */
    public function getQbankPropertyTypeIdForSystemNameReturnsPropertyTypeId(): void
    {
        $systemName = 'keyword';

        $propertyType = new PropertyType([
            'created' => new \DateTime(),
            'createdBy' => 3,
            'updated' => null,
            'updatedBy' => null,
            'deleted' => null,
            'name' => 'Keywords',
            'systemName' => $systemName,
            'description' => 'För att namnge bilder',
            'dataTypeId' => 6,
        ]);

        $repositoryProphecy = $this->prophesize(PropertyTypeRepository::class);
        $repositoryProphecy->findBySystemName($systemName)->shouldBeCalled()->willReturn($propertyType);
        GeneralUtility::setSingletonInstance(PropertyTypeRepository::class, $repositoryProphecy->reveal());

        self::assertSame(
            PropertyUtility::getQbankPropertyTypeIdForSystemName($systemName),
            $propertyType->getDataTypeId()
        );
    }

    /**
     * @test
     */
    public function getQbankPropertyTypeIdForUnknownSystemNameReturnsPropertyTypeIdZero(): void
    {
        $repositoryProphecy = $this->prophesize(PropertyTypeRepository::class);
        GeneralUtility::setSingletonInstance(PropertyTypeRepository::class, $repositoryProphecy->reveal());

        self::assertSame(
            PropertyUtility::getQbankPropertyTypeIdForSystemName('unknownsystemname'),
            0
        );
    }

    /**
     * @test
     */
    public function getLabelForFilePropertyReturnsCorrectLabel(): void
    {
        $fileProperty = 'content_creation_date';

        self::assertSame(
            PropertyUtility::getLabelForFileProperty($fileProperty),
            $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['qbank']['fileProperties'][$fileProperty]['label']
        );
    }

    /**
     * @test
     */
    public function getLabelForFilePropertyThrowsExceptionForUnknownFileProperty(): void
    {
        self::expectException(MissingFilePropertyException::class);

        PropertyUtility::getLabelForFileProperty('unknownfileproperty');
    }
}
