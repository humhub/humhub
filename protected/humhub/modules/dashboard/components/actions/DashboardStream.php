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

namespace humhub\modules\dashboard\components\actions;

use Yii;

/**
 * DashboardStreamAction
 * Note: This stream action is also used for activity e-mail content.
 *
 * @package humhub.modules_core.dashboard
 * @since 0.11
 * @author luke
 */
class DashboardStream extends \humhub\modules\content\components\actions\Stream
{

    public function init()
    {
        parent::init();

        if ($this->user == null) {

            /**
             * For guests collect all wall_ids of "guest" public spaces / user profiles.
             * Generally show only public content
             */
            $publicSpacesSql = Yii::app()->db->createCommand()
                    ->select('si.wall_id')
                    ->from('space si')
                    ->where("si.visibility=" . Space::VISIBILITY_ALL)
                    ->getText();

            $publicProfilesSql = Yii::app()->db->createCommand()
                    ->select('pi.wall_id')
                    ->from('user pi')
                    ->where("pi.status=1 AND pi.visibility=" . User::VISIBILITY_ALL)
                    ->getText();

            $this->criteria->condition .= ' AND (wall_entry.wall_id IN (' . $publicSpacesSql . ') OR wall_entry.wall_id IN (' . $publicProfilesSql . '))';
            $this->criteria->condition .= ' AND content.visibility=' . Content::VISIBILITY_PUBLIC;
        } else {

            /**
             * Collect all wall_ids we need to include into dashboard stream
             */
            // User to user follows
            $userFollow = (new \yii\db\Query())
                    ->select(["uf.wall_id"])
                    ->from('user_follow')
                    ->leftJoin('user uf', 'uf.id=user_follow.object_id AND user_follow.object_model=\'User\'')
                    ->where('user_follow.user_id=' . $this->user->id . ' AND uf.wall_id IS NOT NULL');
            $union = Yii::$app->db->getQueryBuilder()->build($userFollow)[0];

            // User to space follows
            $spaceFollow = (new \yii\db\Query())
                    ->select("sf.wall_id")
                    ->from('user_follow')
                    ->leftJoin('space sf', 'sf.id=user_follow.object_id AND user_follow.object_model="Space"')
                    ->where('user_follow.user_id=' . $this->user->id . ' AND sf.wall_id IS NOT NULL');
            $union .= " UNION " . Yii::$app->db->getQueryBuilder()->build($spaceFollow)[0];

            // User to space memberships
            $spaceMemberships = (new \yii\db\Query())
                    ->select("sm.wall_id")
                    ->from('space_membership')
                    ->leftJoin('space sm', 'sm.id=space_membership.space_id')
                    ->where('space_membership.user_id=' . $this->user->id . ' AND sm.wall_id IS NOT NULL');
            $union .= " UNION " . Yii::$app->db->getQueryBuilder()->build($spaceMemberships)[0];

            // Glue together also with current users wall
            $wallIdsSql = (new \yii\db\Query())
                    ->select('wall_id')
                    ->from('user uw')
                    ->where('uw.id=' . $this->user->id);
            $union .= " UNION " . Yii::$app->db->getQueryBuilder()->build($wallIdsSql)[0];

            // Manual Union (https://github.com/yiisoft/yii2/issues/7992)
            $this->activeQuery->andWhere('wall_entry.wall_id IN (' . $union . ')');

            /**
             * Begin visibility checks regarding the content container
             */
            $this->activeQuery->leftJoin('wall', 'wall_entry.wall_id=wall.id');
            $this->activeQuery->leftJoin(
                    'space_membership', 'wall.object_id=space_membership.space_id AND space_membership.user_id=:userId AND space_membership.status=:status', ['userId' => $this->user->id, ':status' => \humhub\modules\space\models\Membership::STATUS_MEMBER]
            );

            // In case of an space entry, we need to join the space membership to verify the user can see private space content
            $condition = ' (wall.object_model=:userModel AND content.visibility=0 AND content.user_id = :userId) OR ';
            $condition .= ' (wall.object_model=:spaceModel AND content.visibility = 0 AND space_membership.status = ' . \humhub\modules\space\models\Membership::STATUS_MEMBER . ') OR ';
            $condition .= ' (content.visibility = 1 OR content.visibility IS NULL) ';
            $this->activeQuery->andWhere($condition, [':userId' => $this->user->id, ':spaceModel' => \humhub\modules\space\models\Space::className(), ':userModel' => \humhub\modules\user\models\User::className()]);
        }
    }

}
