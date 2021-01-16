<?php

namespace humhub\modules\user;

use humhub\modules\content\models\Content;
use humhub\modules\content\models\ContentContainer;
use humhub\modules\space\MemberEvent;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use humhub\modules\user\models\Password;
use humhub\modules\user\models\Profile;
use humhub\modules\user\models\GroupUser;
use humhub\modules\user\models\Mentioning;
use humhub\modules\user\models\Follow;
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
     * On rebuild of the search index, rebuild all user records
     *
     * @param \yii\base\Event $event
     */
    public static function onSearchRebuild($event)
    {
        foreach (User::find()->visible()->each() as $user) {
            Yii::$app->search->add($user);
        }
    }

    /**
     * On delete of a Content or ContentAddon
     *
     * @param \yii\base\Event $event
     */
    public static function onContentDelete($event)
    {
        Mentioning::deleteAll(['object_model' => $event->sender->className(), 'object_id' => $event->sender->getPrimaryKey()]);
        Follow::deleteAll(['object_model' => $event->sender->className(), 'object_id' => $event->sender->getPrimaryKey()]);
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
            if ($follow->user == null) {
                if ($integrityController->showFix('Deleting follow ' . $follow->id . ' of non existing user!')) {
                    $follow->delete();
                }
            }

            try {
                if ($follow->getTarget() == null) {
                    if ($integrityController->showFix('Deleting follow ' . $follow->id . ' of non target!')) {
                        $follow->delete();
                    }
                }
            } catch (\Exception $e) {
                if ($integrityController->showFix('Deleting follow ' . $follow->id . ' of non target!')) {
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
     * @param \yii\base\Event $event
     */
    public function onSpaceVisibilityChanged($event)
    {
        /** @var Space $space */
        $space = $event->sender;

        $query = Follow::find();
        $query->innerJoin('content', 'user_follow.object_model = content.object_model and user_follow.object_id = content.object_id and content.contentcontainer_id = :contentcontainer_id', [
            ':contentcontainer_id' => $space->contentcontainer_id
        ]);
        $query->leftJoin('space_membership', 'user_follow.user_id=space_membership.user_id and space_membership.space_id = :space_id', [
            ':space_id' => $space->id
        ]);
        $query->where('space_membership.status != 3 or space_membership.status is null');

        /** @var Follow $follow */
        foreach ($query->all() as $follow) {
            $content = Content::findOne(['object_model' => $follow->object_model, 'object_id' => $follow->object_id]);
            $follow->active = $content->canView($follow->user_id);
            $follow->updateAttributes(['active']);
        }
    }

    /**
     * @param \yii\base\Event $event
     */
    public function onContentVisibilityChanged($event)
    {
        /** @var Content $content */
        $content = $event->sender;

        $follows = Follow::findAll(['object_model' => $content->object_model, 'object_id' => $content->object_id]);
        foreach ($follows as $follow) {
            $follow->active = $content->canView($follow->user_id);
            $follow->updateAttributes(['active']);
        }
    }

    /**
     * On add/remove a space membership
     *
     * @param MemberEvent $event
     */
    public function onMemberEvent(MemberEvent $event)
    {
        $query = Follow::find();
        $query->innerJoin('content', 'user_follow.object_model = content.object_model and user_follow.object_id = content.object_id and user_id = :user_id and content.contentcontainer_id = :contentcontainer_id', [
            ':contentcontainer_id' => $event->space->contentcontainer_id,
            ':user_id' => $event->user->id
        ]);

        foreach ($query->all() as $follow) {
            $content = Content::findOne(['object_model' => $follow->object_model, 'object_id' => $follow->object_id]);
            $follow->active = $content->canView($follow->user_id);
            $follow->updateAttributes(['active']);
        }
    }
}
