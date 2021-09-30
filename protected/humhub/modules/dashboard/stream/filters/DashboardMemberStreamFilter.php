<?php

namespace humhub\modules\dashboard\stream\filters;

use humhub\modules\friendship\models\Friendship;
use Yii;
use humhub\modules\content\models\Content;
use humhub\modules\dashboard\Module;
use humhub\modules\space\models\Membership;
use humhub\modules\stream\models\filters\StreamQueryFilter;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;

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
        $this->filterContentVisibility();
        $this->query->addParams([
            ':userId' => $this->user->id,
            ':spaceMembershipStatus' => Membership::STATUS_MEMBER,
            ':spaceEnabledStatus' => Space::STATUS_ENABLED,
            ':userEnabledStatus' => User::STATUS_ENABLED,
            ':userModel' => User::class,
            ':spaceModel' => Space::class,
            ':visibilityPrivate' => Content::VISIBILITY_PRIVATE,
            ':visibilityPublic' => Content::VISIBILITY_PUBLIC,
            ':userContentContainerId' => $this->user->contentcontainer_id
        ]);
    }

    /**
     * Adds joins for container subscription checks
     */
    private function joinWithSubscribedContainers()
    {
        // Join with enabled space containers
        $this->query->leftJoin(
            'space as spaceContainer',
            'spaceContainer.id = contentcontainer.pk AND contentcontainer.class = :spaceModel AND spaceContainer.status = :spaceEnabledStatus'
        );

        // Join with enabled user containers
        $this->query->leftJoin(
            'user AS userContainer',
            'userContainer.id = contentcontainer.pk AND contentcontainer.class = :userModel AND userContainer.status = :userEnabledStatus'
        );

        $this->query->leftJoin(
            'space_membership', 'space_membership.space_id = spaceContainer.id AND space_membership.user_id = :userId AND space_membership.show_at_dashboard = 1 AND space_membership.status = :spaceMembershipStatus'
        );

        if($this->isFollowAllProfilesActive()) {
            // In order to prevent duplicates we only join with space follows in this case
            $this->query->leftJoin(
                'user_follow', 'user_follow.object_id = spaceContainer.id AND user_follow.object_model = :spaceModel AND user_follow.user_id = :userId'
            );
        } else {
            // Otherwise join with all container follows
            $this->query->leftJoin(
                'user_follow', 'contentcontainer.pk = user_follow.object_id AND contentcontainer.class = user_follow.object_model AND user_follow.user_id = :userId'
            );
        }
    }

    /**
     * Filters out containers we are not subscribed to.
     */
    private function filterSubscribedContainer()
    {
        // Only include global content or content from enabled space and user container
        $this->query->andWhere('content.contentcontainer_id IS NULL OR userContainer.id IS NOT NULL OR spaceContainer.id IS NOT NULL');

        // We subscribe to own container, space memberships and following container
        $containerFilterOrContidion = ['OR',
            'space_membership.user_id IS NOT NULL',
            'user_follow.id IS NOT NULL' // In case of "include follow all profiles", this will only include space follows
        ];

        if($this->isFollowAllProfilesActive()) {
            // Everyone follows everyone, so just subscribe to all user containers
            $containerFilterOrContidion[] = 'contentcontainer.class = :userModel';
        } else  {
            // Otherwise only subscribe to own container and friendship containers
            $containerFilterOrContidion[] = 'contentcontainer.id = :userContentContainerId';
        }

        // Filter out non subscribed container
        $this->query->andWhere($containerFilterOrContidion);
    }

    /**
     * Filters content by visibility
     */
    private function filterContentVisibility()
    {
        $visibilityOrCondition = ['OR'];

        // Public content
        $visibilityOrCondition[] = ['OR', 'content.visibility = :visibilityPublic', 'content.visibility IS NULL'];

        // Private content can be seen on own container, member spaces, friend profiles or if the user is the author
        $privateVisibilityOrCondition = ['OR',
            'content.created_by = :userId',
            'content.contentcontainer_id = :userContentContainerId',
            'space_membership.user_id IS NOT NULL'
        ];

        if($this->isFriendShipEnabled()) {
            // Following Friend users can see private content, but only in case friendship was accepted
            $this->query->leftJoin('user_friendship', 'userContainer.id = user_friendship.user_id AND user_friendship.friend_user_id = :userId');
            $privateVisibilityOrCondition[] = ['AND',
                'user_follow.id IS NOT NULL',
                'user_friendship.id IS NOT NULL',
                'EXISTS (SELECT id from user_friendship uf where uf.friend_user_id = user_friendship.user_id AND uf.user_id = user_friendship.friend_user_id)'
            ];
        }

        $visibilityOrCondition[] = ['AND', 'content.visibility = :visibilityPrivate',  $privateVisibilityOrCondition];

        $this->query->andWhere($visibilityOrCondition);
    }

    /**
     * Checks if the friendship module is enabled.
     *
     * @return bool
     */
    private function isFriendShipEnabled()
    {
        return Yii::$app->getModule('friendship')->getIsEnabled();
    }

    /**
     * Checks for the `autoIncludeProfilePosts` module config.
     * @return bool
     */
    private function isFollowAllProfilesActive()
    {
        /* @var $dashboardModule Module */
        $dashboardModule = Yii::$app->getModule('dashboard');
        return $dashboardModule->autoIncludeProfilePosts === Module::STREAM_AUTO_INCLUDE_PROFILE_POSTS_ALWAYS
            || ($dashboardModule->autoIncludeProfilePosts === Module::STREAM_AUTO_INCLUDE_PROFILE_POSTS_ADMIN_ONLY && $this->user->isSystemAdmin());
    }
}
