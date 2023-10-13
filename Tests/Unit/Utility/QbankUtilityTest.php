<?php

declare(strict_types=1);

namespace Pixelant\Qbank\Unit\Utility;

use Pixelant\Qbank\Utility\QbankUtility;
use QBNK\QBank\API\Exception\RequestException;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case.
 *
 * @author Pixelant <info@pixelant.net>
 */
class QbankUtilityTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    /**
     * @test
     */
    public function qbankDateStringToDateTimeReturnsCorrectDateTime(): void
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
    public function qbankDateStringToDateTimeThrowsExceptionOnInvalidDateString(): void
    {
        self::expectException(\InvalidArgumentException::class);

        $stringDate = '2000-06-10 11:13:47';
        QbankUtility::qbankDateStringToDateTime($stringDate);
    }

    /**
     * @test
     */
    public function qbankDateStringToUnixTimestampReturnsCorrectTimeStamp(): void
    {
        $testDateTime = new \DateTime();
        $stringDate = $testDateTime->format(\DateTimeInterface::RFC3339);
        self::assertSame(
            QbankUtility::qbankDateStringToUnixTimestamp($stringDate),
            $testDateTime->getTimestamp()
        );
    }

    /**
     * @test
     */
    public function qbankRequestExceptionStatesMediaIsDeletedReturnsTrueWhenMessageContainsCertainText(): void
    {
        $re = new RequestException('Bad Request: Media is permanently deleted.', 400, new \Exception());

        self::assertTrue(
            QbankUtility::qbankRequestExceptionStatesMediaIsDeleted($re)
        );
    }

    /**
     * @test
     */
    public function qbankRequestExceptionStatesMediaIsDeletedReturnsFalseWhenMessageDoesntContainCertainText(): void
    {
        $re = new RequestException('Bad Request: Media could not be found.', 400, new \Exception());

        self::assertFalse(
            QbankUtility::qbankRequestExceptionStatesMediaIsDeleted($re)
        );
    }
}
