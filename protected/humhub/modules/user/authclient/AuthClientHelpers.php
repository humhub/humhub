<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\authclient;

use humhub\modules\user\models\User;
use humhub\modules\user\services\AuthClientUserService;
use Yii;
use yii\authclient\ClientInterface;

/**
 * AuthClientHelper provides helper functions fo auth clients
 *
 * @since 1.1
 * @author luke
 */
class AuthClientHelpers
{
    /**
     * @param ClientInterface $authClient
     * @param User $user
     * @return void
     * @deprecated since 1.14
     */
    public static function storeAuthClientForUser(ClientInterface $authClient, User $user)
    {
        (new AuthClientUserService($user))->add($authClient);
    }

    public static function generateUsername($attributes): string
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
