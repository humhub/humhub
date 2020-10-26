<?php

namespace humhub\modules\dashboard\stream\filters;

use humhub\modules\dashboard\Module;
use humhub\modules\space\models\Membership;
use humhub\modules\stream\models\filters\StreamQueryFilter;
use Yii;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use yii\db\Query;

/**
 * Stream filter handling dashboard content stream visibility for members of the network.
 *
 * @since 1.8
 */
class DashboardMemberStreamFilter extends StreamQueryFilter
{
    /**
     * @var User
     */
    public $user;

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $friendshipEnabled = Yii::$app->getModule('friendship')->getIsEnabled();

        /* @var $dashboardModule Module */
        $dashboardModule = Yii::$app->getModule('dashboard');

        /**
         * Collect all wall_ids we need to include into dashboard stream
         */
        // Following (User to Space/User)
        $userFollows = (new Query())
            ->select(["contentcontainer.id"])
            ->from('user_follow')
            ->leftJoin('contentcontainer', 'contentcontainer.pk=user_follow.object_id AND contentcontainer.class=user_follow.object_model')
            ->where('user_follow.user_id=' . $this->user->id . ' AND (user_follow.object_model = :spaceClass OR user_follow.object_model = :userClass)');
        $union = Yii::$app->db->getQueryBuilder()->build($userFollows)[0];

        // User to space memberships
        $spaceMemberships = (new Query())
            ->select("contentcontainer.id")
            ->from('space_membership')
            ->leftJoin('space sm', 'sm.id=space_membership.space_id AND sm.status='.Space::STATUS_ENABLED)
            ->leftJoin('contentcontainer', 'contentcontainer.pk=sm.id AND contentcontainer.class = :spaceClass')
            ->where('space_membership.user_id=' . $this->user->id . ' AND space_membership.show_at_dashboard = 1');
        $union .= " UNION " . Yii::$app->db->getQueryBuilder()->build($spaceMemberships)[0];

        if ($friendshipEnabled) {
            // User to user follows
            $usersFriends = (new Query())
                ->select(["ufrc.id"])
                ->from('user ufr')
                ->leftJoin('user_friendship recv', 'ufr.id=recv.friend_user_id AND recv.user_id=' . (int)$this->user->id)
                ->leftJoin('user_friendship snd', 'ufr.id=snd.user_id AND snd.friend_user_id=' . (int)$this->user->id)
                ->leftJoin('contentcontainer ufrc', 'ufr.id=ufrc.pk AND ufrc.class=:userClass')
                ->where('recv.id IS NOT NULL AND snd.id IS NOT NULL AND ufrc.id IS NOT NULL');
            $union .= " UNION " . Yii::$app->db->getQueryBuilder()->build($usersFriends)[0];
        }

        // Automatic include user profile posts without required following
        if ($dashboardModule->autoIncludeProfilePosts == Module::STREAM_AUTO_INCLUDE_PROFILE_POSTS_ALWAYS || (
                $dashboardModule->autoIncludeProfilePosts == Module::STREAM_AUTO_INCLUDE_PROFILE_POSTS_ADMIN_ONLY && Yii::$app->user->isAdmin())) {
            $allUsers = (new Query())->select(["allusers.contentcontainer_id"])->from('user allusers');
            $union .= " UNION " . Yii::$app->db->getQueryBuilder()->build($allUsers)[0];
        }

        // Glue together also with current users wall
        $wallIdsSql = (new Query())
            ->select('cc.id')
            ->from('contentcontainer cc')
            ->where('cc.pk=' . $this->user->id . ' AND cc.class=:userClass');
        $union .= " UNION " . Yii::$app->db->getQueryBuilder()->build($wallIdsSql)[0];

        // Manual Union (https://github.com/yiisoft/yii2/issues/7992)
        $this->query->andWhere('contentcontainer.id IN (' . $union . ') OR contentcontainer.id IS NULL', [':spaceClass' => Space::class, ':userClass' => User::class]);

        /**
         * Begin visibility checks regarding the content container
         */
        $this->query->leftJoin(
            'space_membership', 'contentcontainer.pk=space_membership.space_id AND space_membership.user_id=:userId AND space_membership.status=:status', ['userId' => $this->user->id, ':status' => Membership::STATUS_MEMBER]
        );
        if ($friendshipEnabled) {
            $this->query->leftJoin(
                'user_friendship', 'contentcontainer.pk=user_friendship.user_id AND user_friendship.friend_user_id=:userId', ['userId' => $this->user->id]
            );
        }

        $condition = ' (contentcontainer.class=:userModel AND content.visibility=0 AND content.created_by = :userId) OR ';
        if ($friendshipEnabled) {
            // In case of friendship we can also display private content
            $condition .= ' (contentcontainer.class=:userModel AND content.visibility=0 AND user_friendship.id IS NOT NULL) OR ';
        }

        // In case of an space entry, we need to join the space membership to verify the user can see private space content
        $condition .= ' (contentcontainer.class=:spaceModel AND content.visibility = 0 AND space_membership.status = ' . Membership::STATUS_MEMBER . ') OR ';
        $condition .= ' (content.visibility = 1 OR content.visibility IS NULL) OR';

        // User can see private and public of his own profile (also when not created by hisself)
        $condition .= ' (content.visibility = 0 AND content.contentcontainer_id=:userContentContainerId) ';

        $this->query->andWhere($condition, [
            ':userId' => $this->user->id,
            ':userModel' => User::class,
            ':spaceModel' => Space::class,
            ':userContentContainerId' => $this->user->contentcontainer_id
        ]);
    }
}
