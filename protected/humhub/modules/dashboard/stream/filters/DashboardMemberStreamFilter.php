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

       $this->filterContainerIds($friendshipEnabled);

        /**
         * Begin visibility checks regarding the content container
         */
        $this->query->leftJoin(
            'space_membership', 'contentcontainer.pk=space_membership.space_id AND space_membership.user_id=:userId AND space_membership.status=:spaceMembershipStatus'
        );

        if ($friendshipEnabled) {
            $this->query->leftJoin(
                'user_friendship', 'contentcontainer.pk=user_friendship.user_id AND user_friendship.friend_user_id=:userId'
            );
        }

        $visibilityCondition = ['OR',
            // Public content
            '(content.visibility = 1 OR content.visibility IS NULL)',
            // User can see his own private content
            '(content.visibility = 0 AND content.contentcontainer_id = :userContentContainerId)',
            // User can see private content on profiles if he is author
            '(contentcontainer.class=:userModel AND content.visibility = 0 AND content.created_by = :userId)',
            // User can see private content on spaces he is member of
            '(contentcontainer.class=:spaceModel AND content.visibility = 0 AND space_membership.status = :spaceMembershipStatus)',
        ];

        $this->query->andWhere($visibilityCondition, [
            ':userId' => $this->user->id,
            ':spaceMembershipStatus' => Membership::STATUS_MEMBER,
            ':spaceEnabledStatus' => Space::STATUS_ENABLED,
            ':userModel' => User::class,
            ':spaceModel' => Space::class,
            ':userContentContainerId' => $this->user->contentcontainer_id
        ]);
    }

    /**
     * Includes all container we want to consider in the dashboard query for the given user.
     *
     * @param $friendshipEnabled
     */
    private function filterContainerIds($friendshipEnabled)
    {
        $isFollowAllProfileActive = $this->isFollowAllProfilesActive();

        $containerIdCondition = ['OR'];

        // INCLUDE FOLLOWED SPACES AND USERS
        $containerIdCondition[] =  ['IN', 'contentcontainer.id', (new Query())
            ->select(["contentcontainer.id"])
            ->from('user_follow')
            ->leftJoin('contentcontainer', 'contentcontainer.pk = user_follow.object_id AND contentcontainer.class = user_follow.object_model')
            ->where(['user_follow.user_id' => $this->user->id])
            ->andWhere($isFollowAllProfileActive
                ? ['user_follow.object_model' => Space::class]
                : ['OR', ['user_follow.object_model' => Space::class], ['user_follow.object_model' => User::class]]
            )];

        // INCLUDE MEMBER SPACES
        $containerIdCondition[] =  ['IN', 'contentcontainer.id', (new Query())
            ->select("contentcontainer.id")
            ->from('space_membership')
            ->leftJoin('space sm', 'sm.id = space_membership.space_id AND sm.status = :spaceEnabledStatus')
            ->leftJoin('contentcontainer', 'contentcontainer.pk = sm.id AND contentcontainer.class = :spaceModel')
            ->where(['space_membership.user_id' => $this->user->id])
            ->andWhere(['space_membership.show_at_dashboard' => 1])];


        if ($isFollowAllProfileActive) {
            // INCLUDE ALL USER PROFILES
            // TODO: get rid of user follower related queries in this case
            $containerIdCondition[] =  ['IN', 'contentcontainer.id', (new Query())->select(["allusers.contentcontainer_id"])->from('user allusers')];
        } else if($friendshipEnabled) {
            // INCLUDE FRIEND USERS
            $containerIdCondition[] =  ['IN', 'contentcontainer.id', (new Query())
                ->select(["ufr.contentcontainer_id"])
                ->from('user ufr')
                ->leftJoin('user_friendship recv', 'ufr.id = recv.friend_user_id AND recv.user_id = :userId')
                ->leftJoin('user_friendship snd', 'ufr.id = snd.user_id AND snd.friend_user_id = :userId')
                ->where('recv.id IS NOT NULL AND snd.id IS NOT NULL')];
        }

        // INCLUDE OWN CONTAINER (in case not already included in follow all query)
        if(!$isFollowAllProfileActive) {
            $containerIdCondition[] =  ['contentcontainer.id' => $this->user->contentcontainer_id];
        }

        // Add Global content
        $containerIdCondition[] = 'content.contentcontainer_id IS NULL';

        $this->query->andFilterWhere($containerIdCondition);
    }

    private function isFollowAllProfilesActive()
    {
        /* @var $dashboardModule Module */
        $dashboardModule = Yii::$app->getModule('dashboard');
        return $dashboardModule->autoIncludeProfilePosts == Module::STREAM_AUTO_INCLUDE_PROFILE_POSTS_ALWAYS
            || ($dashboardModule->autoIncludeProfilePosts == Module::STREAM_AUTO_INCLUDE_PROFILE_POSTS_ADMIN_ONLY && $this->user->isSystemAdmin());
    }
}
