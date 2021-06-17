<?php

declare(strict_types=1);

namespace Pixelant\Qbank\Unit\TypeConverter;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use Pixelant\Qbank\TypeConverter\FloatTypeConverter;
use QBNK\QBank\API\Model\PropertyType;

/**
 * Test case.
 *
 * @author Pixelant <info@pixelant.net>
 */
class FloatTypeConverterTest extends UnitTestCase
{
    /**
     * @test
     */
    public function convertFromDataTypeFloatDoesNotAllowArray(): void
    {
        $subject = new FloatTypeConverter();

        self::assertFalse($subject->acceptsArray());
    }

    /**
     * Currently sourceValue is always returned as a float,
     * no need to test varios DataTypes yet.
     * @test
     */
    public function convertFromDataTypeFloatReturnsFloat(): void
    {
        $subject = new FloatTypeConverter();

        self::assertSame(123.45, $subject->convertFrom(123.45, PropertyType::DATATYPE_FLOAT));

        self::assertSame(123.00, $subject->convertFrom(123, PropertyType::DATATYPE_FLOAT));

        self::assertSame(
            123456789.987654321,
            $subject->convertFrom(123456789.987654321, PropertyType::DATATYPE_STRING)
        );

        self::assertSame(-12345.6789, $subject->convertFrom(-12345.6789, PropertyType::DATATYPE_FLOAT));
    }
}
