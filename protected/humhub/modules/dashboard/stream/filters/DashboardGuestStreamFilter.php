<?php

namespace humhub\modules\dashboard\stream\filters;

use humhub\modules\stream\models\filters\StreamQueryFilter;
use Yii;
use humhub\modules\content\models\Content;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use yii\db\Query;

class DashboardGuestStreamFilter extends StreamQueryFilter
{

    /**
     * @inheritDoc
     */
    public function apply()
    {
        /**
         * For guests collect all contentcontainer_ids of "guest" public spaces / user profiles.
         * Generally show only public content
         */
        $publicSpacesSql = (new Query())
            ->select(["contentcontainer.id"])
            ->from('space')
            ->leftJoin('contentcontainer', 'space.id=contentcontainer.pk AND contentcontainer.class=:spaceClass')
            ->where('space.visibility=' . Space::VISIBILITY_ALL)
            ->andWhere('space.status='. Space::STATUS_ENABLED);

        $union = Yii::$app->db->getQueryBuilder()->build($publicSpacesSql)[0];

        $publicProfilesSql = (new Query())
            ->select("contentcontainer.id")
            ->from('user')
            ->leftJoin('contentcontainer', 'user.id=contentcontainer.pk AND contentcontainer.class=:userClass')
            ->where('user.status=1 AND user.visibility = ' . User::VISIBILITY_ALL);
        $union .= " UNION " . Yii::$app->db->getQueryBuilder()->build($publicProfilesSql)[0];

        $this->query->andWhere('content.contentcontainer_id IN (' . $union . ') OR content.contentcontainer_id IS NULL', [':spaceClass' => Space::class, ':userClass' => User::class]);
        $this->query->andWhere(['content.visibility' => Content::VISIBILITY_PUBLIC]);
    }
}
