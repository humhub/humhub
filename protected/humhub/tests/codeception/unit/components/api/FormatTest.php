<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components\api;

use humhub\components\api\Format;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * @see Format
 */
class FormatTest extends HumHubDbTestCase
{
    private function withTimeZone(string $timeZone, callable $fn)
    {
        $previous = Yii::$app->timeZone;
        Yii::$app->timeZone = $timeZone;
        try {
            return $fn();
        } finally {
            Yii::$app->timeZone = $previous;
        }
    }

    public function testStoredDateTimeIsEmittedAsIso8601Utc()
    {
        // Stored values carry no timezone information and are written in the application's
        // timezone — the API reads them as such and answers in UTC.
        $this->withTimeZone('Europe/Berlin', function () {
            // CEST (+02:00) in August
            $this->assertSame('2026-08-22T08:00:00+00:00', Format::dateTime('2026-08-22 10:00:00'));
            // CET (+01:00) in January — the offset must follow DST, not be assumed
            $this->assertSame('2026-01-15T11:00:00+00:00', Format::dateTime('2026-01-15 12:00:00'));
        });
    }

    public function testUtcInstallationRoundTripsUnchanged()
    {
        $this->withTimeZone('UTC', function () {
            $this->assertSame('2026-08-22T10:00:00+00:00', Format::dateTime('2026-08-22 10:00:00'));
        });
    }

    public function testOutputDoesNotDependOnTheCallersProfileTimeZone()
    {
        // The formatter's user timezone must not leak into API output — two callers get the
        // same string for the same record.
        $previous = Yii::$app->formatter->timeZone;

        try {
            $this->withTimeZone('UTC', function () {
                Yii::$app->formatter->timeZone = 'America/New_York';
                $first = Format::dateTime('2026-08-22 10:00:00');

                Yii::$app->formatter->timeZone = 'Asia/Tokyo';
                $second = Format::dateTime('2026-08-22 10:00:00');

                $this->assertSame($first, $second);
                $this->assertSame('2026-08-22T10:00:00+00:00', $first);
            });
        } finally {
            Yii::$app->formatter->timeZone = $previous;
        }
    }

    public function testEmptyValuesBecomeNull()
    {
        $this->assertNull(Format::dateTime(null));
        $this->assertNull(Format::dateTime(''));
    }
}
