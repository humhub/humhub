<?php


/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\helpers;


use humhub\modules\user\Module;
use Yii;

/**
 * Class AuthHelper
 *
 * @since 1.4
 * @package humhub\modules\user\helpers
 */
class AuthHelper
{

    /**
     * Checks if limited access is allowed for unauthenticated users.
     *
     * @return boolean
     */
    public static function isGuestAccessEnabled()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('user');

        if ($module->settings->get('auth.allowGuestAccess')) {
            return true;
        }

        return false;
    }
}
