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
 * HFollowableBehavior Tests
 *
 * @package humhub.tests.unit.behaviors
 * @since 0.9
 * @group user
 * @author luke
 */
class HFollowableBehaviorTest extends CDbTestCase
{

    public $fixtures = array(':user_follow', ':user', ':space');

    public function testFollow()
    {
        // Already followed by fixture
        $user = User::model()->findByPk(2);
        $this->assertTrue($user->follow());

        // Follow 
        $user = User::model()->findByPk(3);
        $this->assertTrue($user->follow());
    }

    public function testUnfollow()
    {
        // Already followed by fixture
        $user = User::model()->findByPk(2);
        $this->assertTrue($user->unfollow());

        $user = User::model()->findByPk(3);
        $this->assertFalse($user->unfollow());
    }

    public function testFollowedBy()
    {

        $user = User::model()->findByPk(2);
        $this->assertTrue($user->isFollowedByUser());

        $user2 = User::model()->findByPk(3);
        $this->assertFalse($user2->isFollowedByUser());
    }

    public function testFollowerCount()
    {
        $user = User::model()->findByPk(2);
        $this->assertEquals(1, $user->getFollowerCount());

        $space = Space::model()->findByPk(3);
        $space->follow(1);
        $space->follow(2);
        $space->follow(3);
        $this->assertEquals(3, $space->getFollowerCount());
    }

    public function testGetFollowers()
    {

        $space = Space::model()->findByPk(3);
        $space->follow(1);
        $space->follow(2);
        $space->follow(3);

        $users = $space->getFollowers();
        $userIds = array_map(create_function('$user', 'return $user->id;'), $users);
        sort($userIds);
        $this->assertEquals(array(1, 2, 3), $userIds);
    }

    public function testGetFollowingCount()
    {
        $user = User::model()->findByPk(1);
        $this->assertEquals($user->getFollowingCount('User'), 1);
    }

    public function testGetFollowingObjects()
    {
        $user = User::model()->findByPk(1);
        $users = $user->getFollowingObjects('User');
        $this->assertEquals($users[0]->id, 2);
    }

}
