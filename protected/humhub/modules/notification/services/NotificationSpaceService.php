<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\services;

use humhub\components\Module;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\models\Content;
use humhub\modules\content\models\ContentContainerSetting;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\user\components\ActiveQueryUser;
use humhub\modules\user\models\Follow;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Exception;

/**
 * The spaces a user follows for notifications, and the users following a container.
 *
 * A user follows a space for notifications when
 *
 * - they are a member of it with `send_notifications` set on their membership, or
 * - they follow it with `send_notifications` set on their follow record, or
 * - it is one of the default notification spaces of the administrator (the module setting
 *   `sendNotificationSpaces`) and they never touched their own notification settings
 *   (no {@see IS_TOUCHED_SETTINGS} user setting).
 *
 * Followers of a container are the audience of e.g. the "new content" notification: for public
 * content the members and followers, for private content the members only.
 *
 * @since 1.20
 */
final class NotificationSpaceService
{
    /**
     * User setting name to know if the user has modified default notification settings
     */
    public const IS_TOUCHED_SETTINGS = 'is_touched_settings';

    /**
     * Checks if the given user is following notifications for the given space.
     * This is the case for members and followers with the sent_notifications settings.
     */
    public function isFollowingSpace(User $user, Space $space): bool
    {
        $membership = $space->getMembership($user);
        if ($membership) {
            return (bool)$membership->send_notifications;
        }

        return $space->isFollowedByUser($user, true);
    }

    /**
     * Returns all notification followers for the given $content instance.
     * This function includes ContentContainer followers only if the content visibility is set to public,
     * else only space members with send_notifications settings are returned.
     *
     * @throws Exception
     */
    public function getFollowers(Content $content): ?ActiveQueryUser
    {
        return $this->getContainerFollowers($content->getContainer(), $content->isPublic());
    }

    /**
     * Returns all notification followers for the given $container. If $public is set to false
     * only members with send_notifications settings are returned.
     */
    public function getContainerFollowers(ContentContainerActiveRecord $container, bool $public = true): ?ActiveQueryUser
    {
        $query = null;

        if ($container instanceof Space) {
            $isDefault = $this->isDefaultNotificationSpace($container);

            $query = $container->getMemberListService()->getNotificationQuery();

            if ($public) {
                // Add explicit follower and non explicit follower if $isDefault
                $query->union($this->findFollowers($container, $isDefault));
            } elseif ($isDefault) {
                // Add all members without explicit following and no notification settings.
                $query->union($container->getMemberListService()->getNotificationQuery(false)
                    ->andWhere(['not exists', $this->findNotExistingSettingSubQuery()]));
            }
        } elseif ($container instanceof User) {
            // Note the notification follow logic for users is currently not implemented.
            // TODO: perhaps return only friends if public is false?

            $query = User::find()->where(['id' => $container->id]);
            if ($public) {
                $query->union(Follow::getFollowersQuery($container, true));
            }
        }

        return $query;
    }

    private function isDefaultNotificationSpace(Space $container): bool
    {
        $defaultSpaces = Yii::$app->getModule('notification')->settings->getSerialized('sendNotificationSpaces');
        return !empty($defaultSpaces) && in_array($container->guid, $defaultSpaces);
    }

    private function findFollowers(Space $container, bool $isDefault = false)
    {
        // Find all followers with send_notifications = 1
        $query = Follow::getFollowersQuery($container, true);

        if ($isDefault) {
            // Add all user with no notification setting
            $query->orWhere([
                'and', 'user.status=1', ['not exists', $this->findNotExistingSettingSubQuery()],
            ]);
        }

        return $query;
    }

    private function findNotExistingSettingSubQuery()
    {
        return ContentContainerSetting::find()
            ->where('contentcontainer_setting.contentcontainer_id=user.contentcontainer_id')
            ->andWhere(['contentcontainer_setting.module_id' => 'notification'])
            ->andWhere(['contentcontainer_setting.name' => self::IS_TOUCHED_SETTINGS]);
    }

    /**
     * Get default notification spaces for the given user.
     *
     * @param User|null $user NULL - to don't filter by user
     * @return Space[]
     */
    public function getDefaultNotificationSpaces(?User $user = null): array
    {
        $spaces = Space::find()
            ->where(['guid' => Yii::$app->getModule('notification')->settings->getSerialized('sendNotificationSpaces')]);

        if ($user) {
            $spaces->visible($user)
                ->filterBlockedSpaces($user);
        }

        return $spaces->all();
    }

    /**
     * Returns all spaces this user is following (including member spaces) with sent_notification setting.
     *
     * @return Space[]
     */
    public function getSpaces(User $user): array
    {
        $memberSpaces = Membership::getUserSpaceQuery($user, true, true)->all();
        $followSpaces = Follow::getFollowedSpacesQuery($user, true)->all();

        $result = array_merge($memberSpaces, $followSpaces);

        if (!self::isTouchedSettings($user)) {
            $result = array_merge($result, $this->getDefaultNotificationSpaces($user));
        }

        return $result;
    }

    /**
     * Whether the user has modified the default notification settings.
     *
     * @throws \Throwable
     */
    public static function isTouchedSettings(User $user): bool
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('notification');
        return (bool)$module->settings->user($user)?->get(self::IS_TOUCHED_SETTINGS);
    }

    /**
     * Returns all spaces this user is not following.
     *
     * @return Space[]
     */
    public function getNonNotificationSpaces(?User $user = null, int $limit = 25): array
    {
        if ($user) {
            $memberSpaces = Membership::getUserSpaceQuery($user, true, false)->limit($limit)->all();
            $limit -= count($memberSpaces);
            $followSpaces = Follow::getFollowedSpacesQuery($user, false)->limit($limit)->all();

            return array_merge($memberSpaces, $followSpaces);
        }

        $defaultSpaces = Yii::$app->getModule('notification')->settings->getSerialized('sendNotificationSpaces');
        return (empty($defaultSpaces)) ? Space::find()->limit($limit)->all() : Space::find()->where(['not in', 'guid', $defaultSpaces])->limit($limit)->all();
    }

    /**
     * Sets the notification space settings for this user (or global if no user is given).
     *
     * Those are the spaces for which the user want to receive ContentCreated Notifications.
     *
     * @param string[] $spaceGuids array of space guids
     */
    public function setSpaces(array $spaceGuids, ?User $user = null)
    {
        if (!$user) { // Note: global notification space settings are currently not active!
            return Yii::$app->getModule('notification')->settings->setSerialized('sendNotificationSpaces', $spaceGuids);
        }

        $spaces = Space::findAll(['guid' => $spaceGuids]);

        // Save actual selection.
        foreach ($spaces as $space) {
            $this->setSpaceSetting($user, $space);
        }

        $spaceIds = array_map(fn($space) => $space->id, $spaces);

        // Update non selected membership spaces
        Membership::updateAll(['send_notifications' => 0], [
            'and',
            ['user_id' => $user->id],
            ['not in', 'space_id', $spaceIds],
        ]);

        // Update non selected following spaces
        Follow::updateAll(['send_notifications' => 0], [
            'and',
            ['user_id' => $user->id],
            ['object_model' => Space::class],
            ['not in', 'object_id', $spaceIds],
        ]);

        return null;
    }

    /**
     * Reset the notification space settings for all users
     */
    public function resetSpaces(): void
    {
        // Reset notifications for all selected membership spaces
        Membership::updateAll(['send_notifications' => 0]);

        // Delete all selected following spaces
        Follow::updateAll(['send_notifications' => 0], ['object_model' => Space::class]);
    }

    /**
     * Sets the send_notifications settings for the given space and user.
     *
     * @param User $user user instance for which this settings will aplly
     * @param Space $space which notifications will be followed / unfollowed
     * @param bool $follow the setting value (true by default)
     */
    public function setSpaceSetting(User $user, Space $space, bool $follow = true): void
    {
        if (!self::isTouchedSettings($user)) {
            // If the user didn't touch the notification settings yet,
            // we need to set the default/global notification spaces for the given user,
            // and mark the user's notification settings as touched.
            // It is required after a new installation or when the notification settings
            // have been reset for all users by admin or for the user himself.
            /* @var Module $module */
            $module = Yii::$app->getModule('notification');
            $module->settings->user($user)?->set(self::IS_TOUCHED_SETTINGS, true);

            foreach ($this->getDefaultNotificationSpaces($user) as $defaultNotifiedSpace) {
                if (!$defaultNotifiedSpace->is($space)) {
                    $this->setSpaceSetting($user, $defaultNotifiedSpace, true);
                }
            }
        }

        $membership = $space->getMembership($user->id);
        if ($membership) {
            $membership->send_notifications = $follow;
            $membership->save();
            return;
        }

        $space->follow($user, $follow);
    }

    /**
     * Check if notifications are sent from the given Space to the given or current user
     */
    public function hasSpace(Space $space, ?User $user = null): bool
    {
        if ($user === null) {
            if (Yii::$app->user->isGuest) {
                return false;
            }
            $user = Yii::$app->user->getIdentity();
        }

        foreach ($this->getSpaces($user) as $notificationSpace) {
            if ($space->is($notificationSpace)) {
                return true;
            }
        }

        return false;
    }
}
