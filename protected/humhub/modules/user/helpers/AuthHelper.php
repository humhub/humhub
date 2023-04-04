<?php


/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\helpers;


use humhub\modules\user\models\User;
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

    /**
     * Find or generates a username based on given attributes provided
     * by an AuthClient.
     *
     * @param $attributes
     * @return string
     * @throws \yii\base\Exception
     */
    public static function generateUsernameByAttributes($attributes): string
    {
        if (isset($attributes['username'])) {
            $user = User::find()->where(['username' => $attributes['username']]);
            if (!$user->exists()) {
                return $attributes['username'];
            }
        }

        $username = [];
        if (isset($attributes['firstname'])) {
            $username[] = $attributes['firstname'];
        }
        if (isset($attributes['lasttname'])) {
            $username[] = $attributes['lasttname'];
        }
        if (isset($attributes['family_name'])) {
            $username[] = $attributes['family_name'];
        }

        if (empty($username)) {
            $username = Yii::$app->security->generateRandomString(8);
        } else {
            $username = implode('_', $username);
        }

        $username = strtolower(substr($username, 0, 32));
        $usernameRandomSuffix = '';
        $user = User::find()->where(['username' => $username . $usernameRandomSuffix]);

        while ($user->exists()) {
            $usernameRandomSuffix = '_' . strtolower(Yii::$app->security->generateRandomString(2));
            $user->where(['username' => $username . $usernameRandomSuffix]);
        }

        return $username . $usernameRandomSuffix;
    }
}
