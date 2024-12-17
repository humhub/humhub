<?php

namespace humhub\modules\dashboard\stream\filters;

use humhub\modules\content\models\Content;
use humhub\modules\space\models\Space;
use humhub\modules\stream\models\filters\StreamQueryFilter;
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
        $publicSpacesSql = (new Query())
            ->select(["contentcontainer_id"])
            ->from('space')
            ->where(['space.visibility' => Space::VISIBILITY_ALL])
            ->andWhere(['space.status' => Space::STATUS_ENABLED]);

        $this->query->andFilterWhere(['OR',
            ['IN', 'content.contentcontainer_id', $publicSpacesSql],
            'content.contentcontainer_id IS NULL',
        ]);
    }
}
