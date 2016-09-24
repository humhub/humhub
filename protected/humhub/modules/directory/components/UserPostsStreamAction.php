<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\directory\components;

use Yii;
use humhub\modules\user\models\User;

/**
 * UserPostsStreamAction
 *
 * @package humhub.modules_core.directory
 * @author luke
 * @since 0.11
 */
class UserPostsStreamAction extends \humhub\modules\content\components\actions\Stream
{

    public function init()
    {
        parent::init();

        $this->activeQuery->andWhere(['content.visibility' => \humhub\modules\content\models\Content::VISIBILITY_PUBLIC]);

        $wallIdsQuery = (new \yii\db\Query())
                ->select('wall_id')
                ->from('user uw');
        if (Yii::$app->user->isGuest) {
            $wallIdsQuery->andWhere('visibility=' . User::VISIBILITY_ALL);
        }
        $wallIdsSql = Yii::$app->db->getQueryBuilder()->build($wallIdsQuery)[0];
        $this->activeQuery->andWhere('wall_entry.wall_id IN (' . $wallIdsSql . ')');
    }

}
