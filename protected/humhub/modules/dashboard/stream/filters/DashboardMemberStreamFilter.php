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
        $this->joinWithSubscribedContainers();
        $this->filterSubscribedContainer();
        $this->filterByContentVisibility();
    }

    private function joinWithSubscribedContainers()
    {
        /**
         * Begin visibility checks regarding the content container
         */
        $this->query->leftJoin(
            'space_membership', 'contentcontainer.pk = space_membership.space_id AND space_membership.user_id = :userId AND space_membership.status = :spaceMembershipStatus'
        );

        $this->query->leftJoin(
            'user_follow', 'contentcontainer.pk = user_follow.object_id AND contentcontainer.class = user_follow.object_model AND user_follow.user_id = :userId'
        );

        if ($this->isFriendShipEnabled()) {
            $this->query->leftJoin(
                'user_friendship', 'contentcontainer.pk = user_friendship.user_id AND contentcontainer.class = :userModel AND user_friendship.friend_user_id = :userId'
            );
        }
    }

    private function isFriendShipEnabled()
    {
        return Yii::$app->getModule('friendship')->getIsEnabled();
    }

    private function filterSubscribedContainer()
    {
        $containerFilterOrContidion = ['OR',
            'contentcontainer.id = :userContentContainerId',
            'space_membership.user_id IS NOT NULL',
            'user_follow.id IS NOT NULL'
        ];

        if($this->isFriendShipEnabled()) {
            $containerFilterOrContidion[] = 'user_friendship.id IS NOT NULL';
        }

        if($this->isFollowAllProfilesActive()) {
            $containerFilterOrContidion = 'contentcontainer.class = :userModel';
        }

        // Filter out non subscribed container
        $this->query->andWhere($containerFilterOrContidion);
    }

    private function filterByContentVisibility()
    {
        $visibilityOrCondition = ['OR'];

        // Public content
        $visibilityOrCondition[] = '(content.visibility = 1 OR content.visibility IS NULL)';

        // Private content can be seen on my own container, member spaces and friends or if the user is the author
        $privateVisibilityOrCondition = '(content.visibility = 0 AND ( ';
        $privateVisibilityOrCondition .= 'content.created_by = :userId ';
        $privateVisibilityOrCondition .= 'OR content.contentcontainer_id = :userContentContainerId ';
        $privateVisibilityOrCondition .= 'OR space_membership.user_id IS NOT NULL ';

        if($this->isFriendShipEnabled()) {
            $privateVisibilityOrCondition .= 'OR user_friendship.id IS NOT NULL ';
        }

        $privateVisibilityOrCondition .= '))';

        $visibilityOrCondition[] = $privateVisibilityOrCondition;

        $this->query->andWhere($visibilityOrCondition, [
            ':userId' => $this->user->id,
            ':spaceMembershipStatus' => Membership::STATUS_MEMBER,
            ':spaceEnabledStatus' => Space::STATUS_ENABLED,
            ':userModel' => User::class,
            ':spaceModel' => Space::class,
            ':userContentContainerId' => $this->user->contentcontainer_id
        ]);
    }

    private function isFollowAllProfilesActive()
    {
        /* @var $dashboardModule Module */
        $dashboardModule = Yii::$app->getModule('dashboard');
        return $dashboardModule->autoIncludeProfilePosts === Module::STREAM_AUTO_INCLUDE_PROFILE_POSTS_ALWAYS
            || ($dashboardModule->autoIncludeProfilePosts === Module::STREAM_AUTO_INCLUDE_PROFILE_POSTS_ADMIN_ONLY && $this->user->isSystemAdmin());
    }
}
