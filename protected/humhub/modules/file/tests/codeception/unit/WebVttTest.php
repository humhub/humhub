<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\file;

use humhub\modules\file\libs\WebVtt;
use tests\codeception\_support\HumHubDbTestCase;

class WebVttTest extends HumHubDbTestCase
{
    private const VALID = "WEBVTT\n\n1\n00:00:01.000 --> 00:00:03.500\nHello\n\n01:02.000 --> 01:04.000 align:start\nWorld\n";

    public function testValidTextHasNoErrors()
    {
        $this->assertSame([], WebVtt::validate(self::VALID));
        $this->assertSame([], WebVtt::validate("WEBVTT - A title\n\n00:00:01.000 --> 00:00:02.000\nHi"));
        $this->assertSame([], WebVtt::validate("\xEF\xBB\xBFWEBVTT\r\n\r\n00:00:01.000 --> 100:00:02.000\r\nHi"));
    }

    public function testMissingHeader()
    {
        $errors = WebVtt::validate("00:00:01.000 --> 00:00:02.000\nHi");

        $this->assertSame(['Line 1: The text must start with "WEBVTT".'], $errors);
        $this->assertNotEmpty(WebVtt::validate("WEBVTTX\n\n00:00:01.000 --> 00:00:02.000\nHi"));
    }

    public function testInvalidTimingsNameTheirLine()
    {
        $errors = WebVtt::validate("WEBVTT\n\n00:00:01.000 --> 00:00:02.000\nOk\n\n00:00:03,000 --> 00:00:04.000\nComma\n\n0:03.000 --> 00:04.000\nShort");

        $this->assertCount(2, $errors);
        $this->assertStringStartsWith('Line 6: Invalid cue timing', $errors[0]);
        $this->assertStringStartsWith('Line 9: Invalid cue timing', $errors[1]);
    }

    public function testStartMustBeBeforeEnd()
    {
        $this->assertSame(
            ['Line 3: The start of the cue must be before its end.'],
            WebVtt::validate("WEBVTT\n\n00:00:05.000 --> 00:00:02.000\nHi"),
        );
        $this->assertNotEmpty(WebVtt::validate("WEBVTT\n\n00:00:02.000 --> 00:00:02.000\nHi"));
    }

    public function testTextWithoutCuesOrEmpty()
    {
        $this->assertSame(['The text contains no cue.'], WebVtt::validate("WEBVTT\n"));
        $this->assertSame(['The text is empty.'], WebVtt::validate("  \n"));
    }

    public function testNormalize()
    {
        $this->assertSame("WEBVTT\n\na\nb\n", WebVtt::normalize("\xEF\xBB\xBFWEBVTT\r\n\r\na\rb\n"));
    }

    public function testSrtDetection()
    {
        $srt = "1\n00:00:01,000 --> 00:00:03,500\nHello\n";

        $this->assertTrue(WebVtt::isSrt($srt));
        $this->assertTrue(WebVtt::isSrt("\xEF\xBB\xBF" . str_replace("\n", "\r\n", $srt)));
        $this->assertFalse(WebVtt::isSrt(self::VALID));
        $this->assertFalse(WebVtt::isSrt("Just some text"));
    }

    public function testFromSrt()
    {
        $srt = "\xEF\xBB\xBF1\r\n00:00:01,000 --> 00:00:03,500\r\nHello, world\r\n\r\n2\r\n00:00:04,000 --> 00:00:05,000\r\n42\r\nLine two\r\n";

        $vtt = WebVtt::fromSrt($srt);

        $this->assertSame("WEBVTT\n\n00:00:01.000 --> 00:00:03.500\nHello, world\n\n00:00:04.000 --> 00:00:05.000\n42\nLine two\n", $vtt);
        $this->assertSame([], WebVtt::validate($vtt));
    }

    public function testFromSrtPadsOneDigitHours()
    {
        $this->assertSame(
            "WEBVTT\n\n00:00:01.000 --> 00:00:02.500\nHi\n\n10:00:01.000 --> 11:00:02.000\nLate\n",
            WebVtt::fromSrt("1\n0:00:01,000 --> 0:00:02,500\nHi\n\n2\n10:00:01,000 --> 11:00:02,000\nLate\n"),
        );
    }

    public function testPrepareConvertsSrtOnly()
    {
        $this->assertSame("WEBVTT\n\n00:00:01.000 --> 00:00:02.000\nHi\n", WebVtt::prepare("1\n00:00:01,000 --> 00:00:02,000\nHi"));
        $this->assertSame(self::VALID, WebVtt::prepare(str_replace("\n", "\r\n", self::VALID)));
    }
}
