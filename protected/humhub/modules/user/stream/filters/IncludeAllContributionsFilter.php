<?php

namespace humhub\modules\user\stream\filters;

use humhub\modules\space\models\Space;
use humhub\modules\stream\models\filters\ContentContainerStreamFilter;
use humhub\modules\user\models\User;
use Yii;

/**
 * This stream query filter manages the scope of a profile stream. This filter supports two scopes
 *
 *  - `StreamQuery[scope] = 'all'`: Include all user related contributions
 *  - `StreamQuery[scope] = 'profile'`: Only include profile posts
 *
 * @since 1.6
 */
class IncludeAllContributionsFilter extends ContentContainerStreamFilter
{

    const SCOPE_ALL = 'all';
    const SCOPE_PROFILE = 'profile';

    /**
     * @var array
     */
    public $scope;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['scope'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        if(!$this->isActive()) {
            return parent::apply();
        }

        $queryUser = $this->streamQuery->user;

        // TODO: Refactor to unify with ActiveQueryContent::readable()
        $this->query->leftJoin('space', 'contentcontainer.pk=space.id AND contentcontainer.class=:spaceClass', [':spaceClass' => Space::class]);
        $this->query->leftJoin('user cuser', 'contentcontainer.pk=cuser.id AND contentcontainer.class=:userClass', [':userClass' => User::class]);

        $this->query->leftJoin('space_membership',
            'contentcontainer.pk=space_membership.space_id AND contentcontainer.class=:spaceClass AND space_membership.user_id=:userId',
            [':userId' => $queryUser->id, ':spaceClass' => Space::class]
        );

        $this->query->andWhere([
            'OR',
            ['content.created_by' => $this->container->id],
            ['content.contentcontainer_id' => $this->container->contentcontainer_id]
        ]);

        if ($queryUser->canViewAllContent()) {
            // Don't restrict if user can view all content:
            $conditionSpaceMembershipRestriction = '';
            $conditionUserPrivateRestriction = '';
        } else {
            // User must be a space's member OR Space and Content are public
            $conditionSpaceMembershipRestriction = ' AND ( space_membership.status=3 OR (content.visibility=1 AND space.visibility != 0) )';
            // User can view only content of own profile
            $conditionUserPrivateRestriction = ' AND content.contentcontainer_id=' . $queryUser->contentcontainer_id;
        }

        // Build Access Check based on Space Content Container
        $conditionSpace = 'space.id IS NOT NULL' . $conditionSpaceMembershipRestriction; // space content

        // Build Access Check based on User Content Container
        $conditionUser = 'cuser.id IS NOT NULL AND (';                  // user content
        $conditionUser .= '   (content.visibility = 1) OR';             // public visible content
        $conditionUser .= '   (content.visibility = 0' . $conditionUserPrivateRestriction . ')';  // private content of user

        if (Yii::$app->getModule('friendship')->getIsEnabled()) {
            $this->query->leftJoin('user_friendship cff', 'cuser.id=cff.user_id AND cff.friend_user_id=:fuid', [':fuid' => $queryUser->id]);
            $conditionUser .= ' OR (content.visibility = 0 AND cff.id IS NOT NULL)';  // users are friends
        }
        $conditionUser .= ')';

        // Created content of is always visible
        $conditionUser .= 'OR content.created_by=' . $queryUser->id;

        $this->query->andWhere("{$conditionSpace} OR {$conditionUser} OR content.contentcontainer_id IS NULL");
    }

    /**
     * @return bool whether or not the include all filter is active or not
     */
    public function isActive()
    {
        return $this->container instanceof User && $this->streamQuery->user !== null && $this->scope === static::SCOPE_ALL;
    }
}
