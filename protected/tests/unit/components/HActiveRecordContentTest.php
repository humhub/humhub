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
 * HActiveRecordContentTest
 *
 * @author luke
 */
class HActiveRecordContentTest extends CDbTestCase
{

    public $fixtures = array(':space', ':space_membership', ':user', ':wall', ':wall_entry', ':post', ':content');

    public function testContentSelectorUser()
    {
        $user = User::model()->findByPk(3);

        // Checks Content Selector only return public posts on other user profiles
        Yii::app()->user->setId(1);
        $posts = Post::model()->contentContainer($user)->findAll();
        $this->assertTrue(count($posts) == 1);
        $this->assertTrue($posts[0]->message == "User 3 Profile Post Public");

        // Check Content Selector returns private & public pusts on own user profile
        Yii::app()->user->setId(3);
        $posts = Post::model()->contentContainer($user)->findAll();
        $this->assertTrue(count($posts) == 2);

        $space = Space::model()->findByPk(1);

        // Check space member can read all posts (public & private)
        Yii::app()->user->setId(1);
        $posts = Post::model()->contentContainer($space)->findAll();
        $this->assertTrue(count($posts) == 3);

        // Check non space member can only see public posts
        Yii::app()->user->setId(2);
        $posts = Post::model()->contentContainer($space)->findAll();
        $this->assertTrue(count($posts) == 2);
    }

}
