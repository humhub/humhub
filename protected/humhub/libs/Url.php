<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2022 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

/**
 * URL Helper
 *
 * @since 1.13
 * @author Luke
 */
class Url extends \yii\helpers\Url
{
    /**
     * Get URL without forcing to host from setting
     *
     * @param string $url
     * @param false $scheme
     * @return string
     */
    public static function toCurrentHost($url = '', $scheme = false): string
    {
        $urlManager = static::getUrlManager();
        $hasProtection = isset($urlManager->protectHost) && $urlManager->protectHost;
        if ($hasProtection) {
            $urlManager->protectHost = false;
        }

        $url = parent::to($url, $scheme);

        if ($hasProtection) {
            $urlManager->protectHost = true;
        }

        return $url;
    }
}
