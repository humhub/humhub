<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\libs;

use DateTime;
use DateTimeZone;
use humhub\libs\TimezoneHelper;
use tests\codeception\_support\HumHubDbTestCase;
use yii\log\Logger;

class TimezoneHelperTest extends HumHubDbTestCase
{
    public function testIsValidAcceptsOnlyIdentifiersUsableAsDefaultTimeZone()
    {
        $this->assertTrue(TimezoneHelper::isValid('UTC'));
        $this->assertTrue(TimezoneHelper::isValid('Europe/Berlin'));

        $this->assertFalse(TimezoneHelper::isValid('Europe/Kiew'));
        // Accepted by DateTimeZone, but rejected by date_default_timezone_set()
        $this->assertFalse(TimezoneHelper::isValid('+02:00'));
        $this->assertFalse(TimezoneHelper::isValid(''));
        $this->assertFalse(TimezoneHelper::isValid(null));
    }

    public function testReplaceUnknownMapsRenamedIdentifierToCurrentName()
    {
        $this->assertSame('Europe/Kyiv', TimezoneHelper::replaceUnknown('Europe/Kiev', 'UTC'));
        $this->assertSame('Asia/Kolkata', TimezoneHelper::replaceUnknown('Asia/Calcutta', 'UTC'));
    }

    public function testReplaceUnknownReturnsFallbackForUnmappedIdentifier()
    {
        $this->assertSame('Europe/Berlin', TimezoneHelper::replaceUnknown('Europe/Kiew', 'Europe/Berlin'));
    }

    public function testReplaceUnknownLogsError()
    {
        static::logInitialize();

        TimezoneHelper::replaceUnknown('Europe/Kiew', 'UTC');

        $this->assertLogRegexCount(1, '/Europe\/Kiew.*UTC/', Logger::LEVEL_ERROR);
    }

    public function testLegacyIdentifiersMapToEquivalentCurrentZones()
    {
        $current = DateTimeZone::listIdentifiers();
        $from = (new DateTime('1970-01-01'))->getTimestamp();
        $to = (new DateTime('2040-01-01'))->getTimestamp();
        $strip = fn(array $transitions) => array_map(fn($t) => [$t['ts'], $t['offset'], $t['isdst']], $transitions);

        foreach (TimezoneHelper::LEGACY_IDENTIFIERS as $legacy => $replacement) {
            $this->assertContains($replacement, $current, "Replacement for $legacy must be a current identifier");
            $this->assertNotContains($legacy, $current, "$legacy is still a current identifier and needs no mapping");

            if (TimezoneHelper::isValid($legacy)) {
                $this->assertSame(
                    $strip((new DateTimeZone($replacement))->getTransitions($from, $to)),
                    $strip((new DateTimeZone($legacy))->getTransitions($from, $to)),
                    "$legacy and $replacement must describe the same time zone since 1970",
                );
            }
        }
    }
}
