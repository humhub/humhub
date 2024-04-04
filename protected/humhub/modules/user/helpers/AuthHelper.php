<?php


/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\helpers;

use humhub\modules\space\models\Space;
use humhub\modules\user\models\forms\Registration;
use humhub\modules\user\models\Invite;
use humhub\modules\user\models\User;
use humhub\modules\user\Module;
use Yii;
use yii\base\Exception;
use yii\web\HttpException;

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
     * @return bool
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
     * @throws Exception
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
        if (isset($attributes['firstname']) && !empty($attributes['firstname'])) {
            $username[] = $attributes['firstname'];
        }
        if (isset($attributes['lastname']) && !empty($attributes['lastname'])) {
            $username[] = $attributes['lastname'];
        } elseif (isset($attributes['family_name']) && !empty($attributes['family_name'])) {
            $username[] = $attributes['family_name'];
        }

        if (empty($username)) {
            $username = Yii::$app->security->generateRandomString(8);
        } else {
            $username = implode('_', $username);
        }

        if (empty($username) || $username === '_') {
            $username = explode("@", $attributes['email'])[0];
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
