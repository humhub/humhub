<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\helpers;

use Detection\MobileDetect;
use Yii;

/**
 * Describes a user agent string in a short, human-readable form, e.g. "Firefox · Linux"
 *
 * @since 1.20
 */
class UserAgentHelper
{
    /**
     * Order matters: Edge and Opera also identify as Chrome, Chrome also as Safari
     */
    private const BROWSERS = [
        'Edge' => '/Edg(e|A|iOS)?\//',
        'Opera' => '/(OPR|Opera)\//',
        'Firefox' => '/(Firefox|FxiOS)\//',
        'Chrome' => '/(Chrome|CriOS)\//',
        'Safari' => '/Version\/[\d.]+.*Safari\//',
    ];

    /**
     * Order matters: iOS also identifies as Mac OS X, Android also as Linux
     */
    private const SYSTEMS = [
        'iOS' => '/iPhone|iPad|iPod/',
        'Android' => '/Android/',
        'Windows' => '/Windows/',
        'macOS' => '/Macintosh|Mac OS X/',
        'Linux' => '/Linux|X11/',
    ];

    public static function describe(?string $userAgent): string
    {
        if (empty($userAgent)) {
            return Yii::t('UserModule.account', 'Unknown device');
        }

        $browser = self::match(self::BROWSERS, $userAgent) ?? Yii::t('UserModule.account', 'Unknown browser');
        $system = self::match(self::SYSTEMS, $userAgent);

        return $system === null ? $browser : $browser . ' · ' . $system;
    }

    public static function isMobile(?string $userAgent): bool
    {
        if (empty($userAgent)) {
            return false;
        }

        $detect = new MobileDetect();
        $detect->setUserAgent($userAgent);

        return $detect->isMobile();
    }

    private static function match(array $patterns, string $userAgent): ?string
    {
        foreach ($patterns as $name => $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return $name;
            }
        }

        return null;
    }
}
