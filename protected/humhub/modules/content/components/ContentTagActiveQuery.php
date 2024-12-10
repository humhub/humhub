<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\components;

use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\user\helpers\AuthHelper;
use humhub\modules\user\models\User;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * Class ContentTagActiveQuery extends for additional filters
 */
class ContentTagActiveQuery extends ActiveQuery
{
    /**
     * Only returns user readable records
     *
     * @param User|null $user
     * @return self
     * @throws \Throwable
     */
    public function readable(?User $user = null): self
    {
        if ($user === null) {
            $user = Yii::$app->user->getIdentity();
        }

        $this->leftJoin('contentcontainer AS rContainer', 'rContainer.id = content_tag.contentcontainer_id')
            ->leftJoin('space AS rSpace', 'rContainer.pk = rSpace.id AND rContainer.class = :spaceClass', [':spaceClass' => Space::class])
            ->leftJoin('user AS rUser', 'rContainer.pk = rUser.id AND rContainer.class = :userClass', [':userClass' => User::class]);

        $conditions = [
            'global' => ['IS', 'content_tag.contentcontainer_id', new Expression('NULL')],
            'space' => ['AND', ['IS NOT', 'rSpace.id', new Expression('NULL')]],
            'user' => ['AND', ['IS NOT', 'rUser.id', new Expression('NULL')]],
        ];

        if ($user !== null) {
            if (!$user->canManageAllContent()) {
                // User must be a space's member OR a space is not private
                $this->leftJoin('space_membership AS rMembership', 'rMembership.space_id = rSpace.id AND rMembership.user_id = :userId', [':userId' => $user->id]);
                $conditions['space'][] = [
                    'OR',
                    ['rMembership.status' => Membership::STATUS_MEMBER],
                    ['!=', 'rSpace.visibility', Space::VISIBILITY_NONE],
                ];
                // User can view only content of own profile
                $conditions['user'][] = ['rUser.id' => $user->id];
            }
        } elseif (AuthHelper::isGuestAccessEnabled()) {
            $conditions['space'][] = ['rSpace.visibility' => Space::VISIBILITY_ALL];
            $conditions['user'][] = ['rUser.visibility' => User::VISIBILITY_ALL];
        } else {
            unset($conditions['space']);
            unset($conditions['user']);
        }

        return $this->andWhere(['OR'] + $conditions);
    }
}
