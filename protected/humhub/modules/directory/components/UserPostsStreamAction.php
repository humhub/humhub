<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

namespace humhub\modules\directory\components;

use Yii;

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
            $wallIdsQuery->andWhere(['visibility' => User::VISIBILITY_ALL]);
        }
        $wallIdsSql = Yii::$app->db->getQueryBuilder()->build($wallIdsQuery)[0];
        $this->activeQuery->andWhere('wall_entry.wall_id IN (' . $wallIdsSql . ')');
    }

}
