<?php

namespace humhub\modules\user;

use Yii;
use humhub\modules\user\models\User;
use humhub\modules\user\models\Password;
use humhub\modules\user\models\Profile;
use humhub\modules\user\models\GroupUser;
use humhub\modules\user\models\Mentioning;
use humhub\modules\user\models\Follow;

/**
 * Events provides callbacks for all defined module events.
 * 
 * @author luke
 */
class Events extends \yii\base\Object
{

    /**
     * On rebuild of the search index, rebuild all user records
     *
     * @param \yii\base\Event $event
     */
    public static function onSearchRebuild($event)
    {
        foreach (models\User::find()->active()->all() as $obj) {
            \Yii::$app->search->add($obj);
        }
    }

    /**
     * On delete of a Content or ContentAddon
     *
     * @param \yii\base\Event $event
     */
    public static function onContentDelete($event)
    {
        models\Mentioning::deleteAll(['object_model' => $event->sender->className(), 'object_id' => $event->sender->getPrimaryKey()]);
        models\Follow::deleteAll(['object_model' => $event->sender->className(), 'object_id' => $event->sender->getPrimaryKey()]);
    }

    /**
     * Callback to validate module database records.
     *
     * @param \yii\base\Event $event
     */
    public static function onIntegrityCheck($event)
    {
        $integrityController = $event->sender;

        $integrityController->showTestHeadline("User Module - Users (" . User::find()->count() . " entries)");
        foreach (User::find()->joinWith(['profile'])->all() as $user) {
            if ($user->profile == null) {
                $integrityController->showWarning("User with id " . $user->id . " has no profile record!");
            }
        }
        
        foreach (GroupUser::find()->joinWith(['user'])->all() as $groupUser) {
            if ($groupUser->user == null) {
                if ($integrityController->showFix("Deleting group admin " . $groupUser->id . " without existing user!")) {
                    $groupUser->delete();
                }
            }
        }

        $integrityController->showTestHeadline("User Module - Password (" . Password::find()->count() . " entries)");
        foreach (Password::find()->joinWith(['user'])->all() as $password) {
            if ($password->user == null) {
                if ($integrityController->showFix("Deleting password " . $password->id . " without existing user!")) {
                    $password->delete();
                }
            }
        }

        $integrityController->showTestHeadline("User Module - Profile (" . Profile::find()->count() . " entries)");
        foreach (Profile::find()->joinWith(['user'])->all() as $profile) {
            if ($profile->user == null) {
                if ($integrityController->showFix("Deleting profile " . $profile->user_id . " without existing user!")) {
                    $profile->delete();
                }
            }
        }

        $integrityController->showTestHeadline("User Module - Mentioning (" . Mentioning::find()->count() . " entries)");
        foreach (Mentioning::find()->joinWith(['user'])->all() as $mentioning) {
            if ($mentioning->user == null) {
                if ($integrityController->showFix("Deleting mentioning " . $mentioning->id . " of non existing user!")) {
                    $mentioning->delete();
                }
            }
            if ($mentioning->getPolymorphicRelation() == null) {
                if ($integrityController->showFix("Deleting mentioning " . $mentioning->id . " of non target!")) {
                    $mentioning->delete();
                }
            }
        }

        $integrityController->showTestHeadline("User Module - Follow (" . Follow::find()->count() . " entries)");
        foreach (Follow::find()->joinWith(['user'])->all() as $follow) {
            if ($follow->user == null) {
                if ($integrityController->showFix("Deleting follow " . $follow->id . " of non existing user!")) {
                    $follow->delete();
                }
            }
            if ($follow->getTarget() == null) {
                if ($integrityController->showFix("Deleting follow " . $follow->id . " of non target!")) {
                    $follow->delete();
                }
            }
        }

        $integrityController->showTestHeadline("User Module - Modules (" . models\Module::find()->count() . " entries)");
        foreach (models\Module::find()->joinWith(['user'])->all() as $module) {
            if ($module->user == null) {
                if ($integrityController->showFix("Deleting user-module " . $module->id . " of non existing user!")) {
                    $module->delete();
                }
            }
        }
    }

    /**
     * Tasks on hourly cron job
     * 
     * @param \yii\base\Event $event
     */
    public static function onHourlyCron($event)
    {
        foreach (Yii::$app->authClientCollection->getClients() as $authClient) {
            if ($authClient instanceof authclient\interfaces\AutoSyncUsers) {
                /**
                 * @var authclient\interfaces\AutoSyncUsers $authClient 
                 */
                $authClient->syncUsers();
            }
        }

        // Delete expired session
        foreach (models\Session::find()->where(['<', 'expire', time()])->all() as $session) {
            $session->delete();
        }
    }

}
