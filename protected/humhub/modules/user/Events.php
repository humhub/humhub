<?php

namespace humhub\modules\user;

use Exception;
use humhub\components\behaviors\PolymorphicRelation;
use humhub\components\Event;
use humhub\helpers\ControllerHelper;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\models\ContentContainer;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\user\models\Follow;
use humhub\modules\user\models\GroupUser;
use humhub\modules\user\models\Mentioning;
use humhub\modules\user\models\Password;
use humhub\modules\user\models\Profile;
use humhub\modules\user\models\User;
use humhub\modules\user\permissions\PeopleAccess;
use Yii;
use yii\base\BaseObject;

/**
 * Events provides callbacks for all defined module events.
 *
 * @author luke
 */
class Events extends BaseObject
{
    /**
     * On delete of a Content or ContentAddon
     *
     * @param \yii\base\Event $event
     */
    public static function onContentDelete($event)
    {
        /* @var ContentActiveRecord $content */
        $content = $event->sender;

        Mentioning::deleteAll(['object_model' => PolymorphicRelation::getObjectModel($content), 'object_id' => $content->getPrimaryKey()]);
        Follow::deleteAll(['object_model' => PolymorphicRelation::getObjectModel($content), 'object_id' => $content->getPrimaryKey()]);
    }

    /**
     * Callback to validate module database records.
     *
     * @param \yii\base\Event $event
     */
    public static function onIntegrityCheck($event)
    {
        $integrityController = $event->sender;

        $integrityController->showTestHeadline('User Module - ContentContainer (' . User::find()->count() . ' entries)');
        foreach (User::find()->joinWith(['contentContainerRecord'])->each() as $user) {
            if ($user->contentContainerRecord === null) {
                if ($integrityController->showFix('Deleting user ' . $user->id . ' without content container record!')) {
                    $user->delete();
                }
            }
        }

        $integrityController->showTestHeadline('User Module - Users (' . User::find()->count() . ' entries)');
        foreach (User::find()->joinWith(['profile'])->each() as $user) {
            if ($user->profile->isNewRecord) {
                $integrityController->showWarning('User with id ' . $user->id . ' has no profile record!');
            }
        }

        foreach (GroupUser::find()->joinWith(['user'])->each() as $groupUser) {
            if ($groupUser->user == null) {
                if ($integrityController->showFix('Deleting group admin ' . $groupUser->id . ' without existing user!')) {
                    $groupUser->delete();
                }
            }
        }

        $integrityController->showTestHeadline('User Module - Password (' . Password::find()->count() . ' entries)');
        foreach (Password::find()->joinWith(['user'])->each() as $password) {
            if ($password->user == null) {
                if ($integrityController->showFix('Deleting password ' . $password->id . ' without existing user!')) {
                    $password->delete();
                }
            }
        }

        $integrityController->showTestHeadline('User Module - Profile (' . Profile::find()->count() . ' entries)');
        foreach (Profile::find()->joinWith(['user'])->each() as $profile) {
            if ($profile->user == null) {
                if ($integrityController->showFix('Deleting profile ' . $profile->user_id . ' without existing user!')) {
                    $profile->delete();
                }
            }
        }

        $integrityController->showTestHeadline('User Module - Mentioning (' . Mentioning::find()->count() . ' entries)');
        foreach (Mentioning::find()->joinWith(['user'])->each() as $mentioning) {
            if ($mentioning->user == null) {
                if ($integrityController->showFix('Deleting mentioning ' . $mentioning->id . ' of non existing user!')) {
                    $mentioning->delete();
                }
            }
            if ($mentioning->getPolymorphicRelation() == null) {
                if ($integrityController->showFix('Deleting mentioning ' . $mentioning->id . ' of non target!')) {
                    $mentioning->delete();
                }
            }
        }

        $integrityController->showTestHeadline('User Module - Follow (' . Follow::find()->count() . ' entries)');
        foreach (Follow::find()->joinWith(['user'])->each() as $follow) {
            if ($follow->user === null) {
                if ($integrityController->showFix('Deleting follow ' . $follow->id . ' of non existing user #' . $follow->user_id . '!')) {
                    $follow->delete();
                }
            }

            try {
                if ($follow->getTarget() === null) {
                    if ($integrityController->showFix('Deleting follow ' . $follow->id . ' of non target ' . $follow->object_model . ' #' . $follow->object_id . '!')) {
                        $follow->delete();
                    }
                }
            } catch (Exception $e) {
                if ($integrityController->showFix('Deleting follow ' . $follow->id . ' of non target ' . $follow->object_model . ' #' . $follow->object_id . '!')) {
                    $follow->delete();
                }
            }
        }

        $userIds = User::find()->select('id')->asArray()->all();
        foreach ($userIds as $key => $id) {
            $userIds[$key] = $id['id'];
        }
        $integrityController->showTestHeadline('User Module - Content container (' . ContentContainer::find()->count() . ' entries)');
        foreach (ContentContainer::find()->where(['NOT IN', 'owner_user_id', $userIds])->each() as $contentContainer) {
            if ($contentContainer['class'] == User::class && $contentContainer['pk'] == $contentContainer['owner_user_id']) {
                if ($integrityController->showFix('Deleting content container ' . $contentContainer->id . ' without existing user!')) {
                    $contentContainer->delete();
                }
            }
        }
    }

    /**
     * Tasks on daily cron job
     *
     * @param \yii\base\Event $event
     */
    public static function onDailyCron($event)
    {
        Yii::$app->queue->push(new jobs\CleanupInvites());
    }

    /**
     * Tasks on hourly cron job
     *
     * @param \yii\base\Event $event
     */
    public static function onHourlyCron($event)
    {
        Yii::$app->queue->push(new jobs\SyncUsers());
        Yii::$app->queue->push(new jobs\DeleteExpiredSessions());
    }

    /**
     * On build of the TopMenu
     *
     * @param Event $event
     */
    public static function onTopMenuInit($event)
    {
        if (Yii::$app->user->isGuest) {
            return;
        }

        if (!Yii::$app->user->can(PeopleAccess::class)) {
            return;
        }

        $event->sender->addEntry(new MenuLink([
            'id' => 'people',
            'icon' => 'users',
            'label' => Yii::t('UserModule.base', 'People'),
            'url' => ['/user/people'],
            'sortOrder' => 200,
            'isActive' => ControllerHelper::isActivePath('user', 'people'),
        ]));
    }

}
