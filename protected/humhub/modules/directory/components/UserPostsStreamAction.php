<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\directory\components;

use Yii;
use humhub\modules\stream\actions\Stream;
use humhub\modules\user\models\User;
use humhub\modules\content\models\Content;
use yii\db\Query;

/**
 * UserPostsStreamAction creates a stream of all public profile posts.
 *
 * @author luke
 * @since 0.11
 */
class UserPostsStreamAction extends Stream
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $query = $this->streamQuery->query();
        $query->andWhere(['content.visibility' => Content::VISIBILITY_PUBLIC]);

        $wallIdsQuery = (new Query())
                ->select('contentcontainer.id')
                ->from('user')
                ->leftJoin('contentcontainer', 'contentcontainer.pk=user.id AND contentcontainer.class=:userClass');
        if (Yii::$app->user->isGuest) {
            $wallIdsQuery->andWhere('visibility=' . User::VISIBILITY_ALL);
        }
        $wallIdsSql = Yii::$app->db->getQueryBuilder()->build($wallIdsQuery)[0];
        $query->andWhere('content.contentcontainer_id IN (' . $wallIdsSql . ')', [':userClass' => User::class]);
    }

}
