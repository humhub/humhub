<?php

namespace humhub\modules\dashboard\stream\filters;

use humhub\modules\dashboard\Module;
use humhub\modules\friendship\models\Friendship;
use humhub\modules\space\models\Membership;
use humhub\modules\stream\models\filters\StreamQueryFilter;
use humhub\modules\user\models\Follow;
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

        /**
         * Begin visibility checks regarding the content container
         */
        $this->query->leftJoin(
            'space_membership', 'contentcontainer.pk=space_membership.space_id AND space_membership.user_id=:userId AND space_membership.status=:spaceMembershipStatus'
        );

        if ($friendshipEnabled) {
            $this->query->leftJoin(
                'user_friendship', 'contentcontainer.pk=user_friendship.user_id AND user_friendship.friend_user_id=:userId AND contentcontainer.class=:userModel'
            );
        }

        $this->filterContainerIds($friendshipEnabled);

        $visibilityOrCondition = ['OR',
            // Public content
            '(content.visibility = 1 OR content.visibility IS NULL)',
            // User can see his own private content
            '(content.visibility = 0 AND content.contentcontainer_id = :userContentContainerId)',
            // User can see private content on profiles if he is author
            '(contentcontainer.class=:userModel AND content.visibility = 0 AND content.created_by = :userId)',
            // User can see private content on spaces he is member of
            '(contentcontainer.class=:spaceModel AND content.visibility = 0 AND space_membership.status = :spaceMembershipStatus)',
        ];

        if($friendshipEnabled) {
            $visibilityOrCondition[] = '(content.visibility=0 AND user_friendship.id IS NOT NULL)';
        }

        $this->query->andWhere($visibilityOrCondition, [
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
        $containerIdCondition[] =  ['IN', 'contentcontainer.id',
            Follow::getFollowedContainerIdQuery($this->user, $isFollowAllProfileActive ? Space::class : null)
        ];


        // INCLUDE MEMBER SPACES
        $containerIdCondition[] =  ['IN', 'contentcontainer.id',
            Membership::getMemberSpaceContainerIdQuery($this->user)->andWhere('sm.show_at_dashboard = 1')
        ];

        if ($isFollowAllProfileActive) {
            // INCLUDE ALL USER PROFILES
            // TODO: get rid of user follower related queries in this case
            $containerIdCondition[] =  ['IN', 'contentcontainer.id', (new Query())->select(["allusers.contentcontainer_id"])->from('user allusers')];
        } else if($friendshipEnabled) {
            // INCLUDE FRIEND USERS
            $containerIdCondition[] =  ['IN', 'contentcontainer.id', Friendship::getFriendshipContainerIdQuery($this->user)];
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
        return $dashboardModule->autoIncludeProfilePosts === Module::STREAM_AUTO_INCLUDE_PROFILE_POSTS_ALWAYS
            || ($dashboardModule->autoIncludeProfilePosts === Module::STREAM_AUTO_INCLUDE_PROFILE_POSTS_ADMIN_ONLY && $this->user->isSystemAdmin());
    }
}
