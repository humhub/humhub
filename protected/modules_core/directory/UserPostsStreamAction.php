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

/**
 * UserPostsStreamAction
 *
 * @package humhub.modules_core.directory
 * @author luke
 * @since 0.11
 */
class UserPostsStreamAction extends BaseStreamAction
{

    public function init()
    {
        parent::init();

        // Build subselect to create a list of user wall_ids
        $wallIdSelectCriteria = new CDbCriteria();
        $wallIdSelectCriteria->select = 'wall_id';

        if (Yii::app()->user->isGuest) {
            $wallIdSelectCriteria->condition = 'visibility=' . User::VISIBILITY_ALL;
        }
        $wallIdSelectSql = User::model()->getCommandBuilder()->createFindCommand(User::model()->getTableSchema(), $wallIdSelectCriteria)->getText();

        $this->criteria->condition .= ' AND wall_entry.wall_id IN (' . $wallIdSelectSql . ')';
        $this->criteria->condition .= " AND content.visibility=" . Content::VISIBILITY_PUBLIC;
    }

}
