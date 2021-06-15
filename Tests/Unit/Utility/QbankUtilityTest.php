<?php

declare(strict_types=1);

namespace Pixelant\Qbank\Unit\Utility;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use Pixelant\Qbank\Utility\QbankUtility;

/**
 * Test case.
 *
 * @author Pixelant <info@pixelant.net>
 */
class QbankUtilityTest extends UnitTestCase
{
    protected $resetSingletonInstances = true;

    /**
     * @test
     */
    public function qbankDateStringToDateTimeReturnsCorrectDateTime()
    {
        $stringDate = '2000-06-10T11:13:47+02:00';
        self::assertSame(
            QbankUtility::qbankDateStringToDateTime($stringDate)->format('Y-m-d\TH:i:sP'),
            '2000-06-10T11:13:47+02:00'
        );

        $stringDate = '2000-06-10T11:13:47Z';
        self::assertSame(
            QbankUtility::qbankDateStringToDateTime($stringDate)->format('Y-m-d\TH:i:sP'),
            '2000-06-10T11:13:47+00:00'
        );
    }

    /**
     * @test
     */
    public function qbankDateStringToDateTimeThrowsExceptionOnInvalidDateString()
    {
        self::expectException(\InvalidArgumentException::class);

        $stringDate = '2000-06-10 11:13:47';
        QbankUtility::qbankDateStringToDateTime($stringDate);

    }
    /**
     * @test
     */
    public function qbankDateStringToUnixTimestampReturnsCorrectTimeStamp()
    {
        $testDateTime = new \DateTime();
        $stringDate = $testDateTime->format(\DateTimeInterface::RFC3339);
        self::assertSame(
            QbankUtility::qbankDateStringToUnixTimestamp($stringDate),
            $testDateTime->getTimestamp()
        );
    }
}
