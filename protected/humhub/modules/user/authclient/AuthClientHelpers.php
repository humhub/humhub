<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\authclient;

use humhub\modules\user\models\Auth;
use humhub\modules\user\models\User;
use Yii;
use yii\authclient\ClientInterface;
use yii\helpers\VarDumper;

/**
 * AuthClientHelper provides helper functions fo auth clients
 *
 * @since 1.1
 * @author luke
 */
class AuthClientHelpers
{

    /**
     * Returns the user object which is linked against given authClient
     *
     * @param ClientInterface $authClient the authClient
     * @return User the user model or null if not found
     */
    public static function getUserByAuthClient(ClientInterface $authClient)
    {
        $attributes = $authClient->getUserAttributes();

        if ($authClient instanceof interfaces\PrimaryClient) {
            /* @var $authClient \humhub\modules\user\authclient\interfaces\PrimaryClient */
            return $authClient->getUser();
        }

        $auth = Auth::find()->where(['source' => $authClient->getId(), 'source_id' => $attributes['id']])->one();
        if ($auth !== null) {
            return $auth->user;
        }
    }

    /**
     * Stores an authClient to an user record
     *
     * @param \yii\authclient\BaseClient $authClient
     * @param User $user
     */
    public static function storeAuthClientForUser(ClientInterface $authClient, User $user)
    {
        $attributes = $authClient->getUserAttributes();

        if ($authClient instanceof interfaces\PrimaryClient) {
            $user->auth_mode = $authClient->getId();
            $user->save();
        } else {
            $auth = Auth::findOne(['source' => $authClient->getId(), 'source_id' => $attributes['id']]);

            /**
             * Make sure authClient is not double assigned
             */
            if ($auth !== null && $auth->user_id != $user->id) {
                $auth->delete();
                $auth = null;
            }


            if ($auth === null) {
                $auth = new \humhub\modules\user\models\Auth([
                    'user_id' => $user->id,
                    'source' => (string)$authClient->getId(),
                    'source_id' => (string)$attributes['id'],
                ]);

                $auth->save();
            }
        }
    }

    /**
     * Removes Authclient for a user
     *
     * @param \yii\authclient\BaseClient $authClient
     * @param User $user
     */
    public static function removeAuthClientForUser(ClientInterface $authClient, User $user)
    {
        Auth::deleteAll([
            'user_id' => $user->id,
            'source' => (string)$authClient->getId()
        ]);
    }

    /**
     * Updates (or creates) a user in HumHub using AuthClients Attributes
     * This method will be called after login or by cron sync.
     *
     * @param \yii\authclient\BaseClient $authClient
     * @param User $user
     * @return boolean succeed
     */
    public static function updateUser(ClientInterface $authClient, User $user = null)
    {
        if ($user === null) {
            $user = self::getUserByAuthClient($authClient);
            if ($user === null) {
                return false;
            }
        }

        $authClient->trigger(BaseClient::EVENT_UPDATE_USER, new \yii\web\UserEvent(['identity' => $user]));

        if ($authClient instanceof interfaces\SyncAttributes) {
            $attributes = $authClient->getUserAttributes();
            foreach ($authClient->getSyncAttributes() as $attributeName) {
                if (isset($attributes[$attributeName])) {
                    if ($user->hasAttribute($attributeName) && !in_array($attributeName, ['id', 'guid', 'status', 'contentcontainer_id', 'auth_mode'])) {
                        $user->setAttribute($attributeName, $attributes[$attributeName]);
                    } else {
                        $user->profile->setAttribute($attributeName, $attributes[$attributeName]);
                    }
                } else {
                    if ($user->profile->hasAttribute($attributeName)) {
                        $user->profile->setAttribute($attributeName, '');
                    }
                }
            }

            if (count($user->getDirtyAttributes()) !== 0 && !$user->save()) {

                Yii::warning('Could not update user (' . $user->id . '). Error: '
                    . VarDumper::dumpAsString($user->getErrors()), 'user');

                return false;
            }

            if (count($user->profile->getDirtyAttributes()) !== 0 && !$user->profile->save()) {
                Yii::warning('Could not update user profile (' . $user->id . '). Error: '
                    . VarDumper::dumpAsString($user->profile->getErrors()), 'user');
                return false;
            }
        }

        return true;
    }

    /**
     * Populates a Registration model with the information provided by the given AuthClient
     *
     * @param ClientInterface $authClient
     * @return bool|\humhub\modules\user\models\forms\Registration|null
     */
    public static function createRegistration(ClientInterface $authClient)
    {
        $attributes = $authClient->getUserAttributes();

        if (!isset($attributes['id'])) {
            return null;
        }

        $registration = new \humhub\modules\user\models\forms\Registration();
        $registration->enablePasswordForm = false;
        $registration->enableEmailField = true;

        if ($authClient instanceof interfaces\ApprovalBypass) {
            $registration->enableUserApproval = false;
        }

        unset($attributes['id']);
        $registration->getUser()->setAttributes($attributes, false);
        $registration->getProfile()->setAttributes($attributes, false);
        $registration->getGroupUser()->setAttributes($attributes, false);

        return $registration;
    }


    /**
     * Automatically creates user by auth client attributes
     *
     * @param \yii\authclient\BaseClient $authClient
     * @return User the created user
     */
    public static function createUser(ClientInterface $authClient)
    {
        $registration = static::createRegistration($authClient);
        if ($registration !== null && $registration->validate() && $registration->register($authClient)) {
            return $registration->getUser();
        }

        return null;
    }

    /**
     * Returns all users which are using an given authclient
     *
     * @param ClientInterface $authClient
     * @return \yii\db\ActiveQuery
     */
    public static function getUsersByAuthClient(ClientInterface $authClient)
    {
        $query = User::find();

        if ($authClient instanceof interfaces\PrimaryClient) {
            $query->where([
                'auth_mode' => $authClient->getId()
            ]);
        } else {
            $query->where(['user_auth.source' => $authClient->getId()]);
        }

        return $query;
    }

    /**
     * Returns AuthClients used by given User
     *
     * @param User $user
     * @return ClientInterface[] the users authclients
     */
    public static function getAuthClientsByUser(User $user)
    {
        $authClients = [];

        foreach (Yii::$app->authClientCollection->getClients() as $client) {
            /* @var $client ClientInterface */

            // Add primary authClient
            if ($user->auth_mode == $client->getId()) {
                $authClients[] = $client;
            }

            // Add additional authClient
            foreach ($user->auths as $auth) {
                if ($auth->source == $client->getId()) {
                    $authClients[] = $client;
                }
            }
        }

        return $authClients;
    }

    /**
     * Returns a list of all synchornized user attributes
     *
     * @param User $user
     * @return array attribute names
     */
    public static function getSyncAttributesByUser(User $user)
    {
        $attributes = [];
        foreach (self::getAuthClientsByUser($user) as $authClient) {
            if ($authClient instanceof interfaces\SyncAttributes) {
                $attributes = array_merge($attributes, $authClient->getSyncAttributes());
            }
        }
        return $attributes;
    }

}
