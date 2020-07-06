<?php

namespace humhub\modules\user\stream\filters;

use humhub\modules\space\models\Space;
use humhub\modules\stream\models\filters\StreamQueryFilter;
use humhub\modules\user\models\User;
use Yii;

class IncludeAllContributionsFilter extends StreamQueryFilter
{
    const ID = 'includeAllContributions';

    /**
     * @var User
     */
    public $user;

    public $filters = [];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['filters'], 'safe']
        ];
    }

    public function apply()
    {
        if(!$this->isActive()) {
            return;
        }

        $this->query->leftJoin('space', 'contentcontainer.pk=space.id AND contentcontainer.class=:spaceClass', [':spaceClass' => Space::class]);
        $this->query->leftJoin('user cuser', 'contentcontainer.pk=cuser.id AND contentcontainer.class=:userClass', [':userClass' => User::class]);

        $this->query->leftJoin('space_membership',
            'contentcontainer.pk=space_membership.space_id AND contentcontainer.class=:spaceClass AND space_membership.user_id=:userId',
            [':userId' => $this->user->id, ':spaceClass' => Space::class]
        );

        $this->query->andWhere([
            'OR',
            ['content.created_by' => $this->user->id],
            ['content.contentcontainer_id' => $this->user->contentcontainer_id]
        ]);

        // Build Access Check based on Space Content Container
        $conditionSpace = 'space.id IS NOT NULL AND (';                              // space content
        $conditionSpace .= ' (space_membership.status=3)';                           // user is space member
        $conditionSpace .= ' OR (content.visibility=1 AND space.visibility != 0)';   // visibile space and public content
        $conditionSpace .= ')';

        // Build Access Check based on User Content Container
        $conditionUser = 'cuser.id IS NOT NULL AND (';                  // user content
        $conditionUser .= '   (content.visibility = 1) OR';             // public visible content
        $conditionUser .= '   (content.visibility = 0 AND content.contentcontainer_id=' . $this->user->contentContainerRecord->id . ')';  // private content of user

        if (Yii::$app->getModule('friendship')->getIsEnabled()) {
            $this->query->leftJoin('user_friendship cff', 'cuser.id=cff.user_id AND cff.friend_user_id=:fuid', [':fuid' => $this->user->id]);
            $conditionUser .= ' OR (content.visibility = 0 AND cff.id IS NOT NULL)';  // users are friends
        }
        $conditionUser .= ')';

        // Created content of is always visible
        $conditionUser .= 'OR content.created_by=' . $this->user->id;

        $this->query->andWhere("{$conditionSpace} OR {$conditionUser} OR content.contentcontainer_id IS NULL");
    }

    public function isActive()
    {
        return in_array(static::ID, $this->filters);
    }
}
