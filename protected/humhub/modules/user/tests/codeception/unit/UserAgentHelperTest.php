<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit;

use humhub\modules\user\helpers\UserAgentHelper;
use tests\codeception\_support\HumHubDbTestCase;

class UserAgentHelperTest extends HumHubDbTestCase
{
    public static function userAgentProvider(): array
    {
        return [
            'firefox linux' => ['Mozilla/5.0 (X11; Linux x86_64; rv:131.0) Gecko/20100101 Firefox/131.0', 'Firefox · Linux', false],
            'chrome windows' => ['Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36', 'Chrome · Windows', false],
            'edge windows' => ['Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36 Edg/129.0.0.0', 'Edge · Windows', false],
            'opera macos' => ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36 OPR/114.0.0.0', 'Opera · macOS', false],
            'safari macos' => ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.0 Safari/605.1.15', 'Safari · macOS', false],
            'safari iphone' => ['Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.0 Mobile/15E148 Safari/604.1', 'Safari · iOS', true],
            'chrome iphone' => ['Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/129.0.6668.69 Mobile/15E148 Safari/604.1', 'Chrome · iOS', true],
            'chrome android' => ['Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Mobile Safari/537.36', 'Chrome · Android', true],
            'unknown' => ['curl/8.10.1', 'Unknown browser', false],
        ];
    }

    /**
     * @dataProvider userAgentProvider
     */
    public function testDescribe(string $userAgent, string $expected, bool $isMobile): void
    {
        $this->assertSame($expected, UserAgentHelper::describe($userAgent));
        $this->assertSame($isMobile, UserAgentHelper::isMobile($userAgent));
    }

    public function testDescribeEmpty(): void
    {
        $this->assertSame('Unknown device', UserAgentHelper::describe(null));
        $this->assertSame('Unknown device', UserAgentHelper::describe(''));
        $this->assertFalse(UserAgentHelper::isMobile(null));
    }
}
