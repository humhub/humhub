<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2022 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use humhub\components\console\UrlManager;
use yii\helpers\BaseUrl;

/**
 * SafeBaseUrl Helper to use host from general setting "Base URL"
 *
 * @since 1.13
 * @author Luke
 */
class SafeBaseUrl extends BaseUrl
{
    /**
     * @inheritdoc
     */
    protected static function getUrlManager()
    {
        return static::$urlManager ?: new UrlManager();
    }
}
