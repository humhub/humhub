<?php

namespace humhub\modules\dashboard\stream\filters;

use humhub\modules\stream\models\filters\StreamQueryFilter;
use Yii;
use humhub\modules\content\models\Content;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use yii\db\Query;

/**
 * Stream filter handling dashboard content stream visibility of guest users.
 *
 * @since 1.8
 */
class DashboardGuestStreamFilter extends StreamQueryFilter
{

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $this->query->andWhere(['content.visibility' => Content::VISIBILITY_PUBLIC]);

        /**
         * For guests collect all contentcontainer_ids of "guest" public spaces / user profiles.
         * Generally show only public content
         */
        $publicSpacesSql = Space::find()
            ->select(["contentcontainer_id"])
            ->whereState(Space::STATE_ENABLED)
            ->andWhere(['space.visibility' =>  Space::VISIBILITY_ALL]);

        $publicProfilesSql = User::find()
            ->select("contentcontainer_id")
            ->whereState(User::STATE_ENABLED)
            ->andWhere(['user.visibility' =>  User::VISIBILITY_ALL]);

        $this->query->andFilterWhere(['OR',
            ['IN', 'content.contentcontainer_id', $publicSpacesSql],
            ['IN', 'content.contentcontainer_id', $publicProfilesSql],
            'content.contentcontainer_id IS NULL',
        ]);
    }
}
