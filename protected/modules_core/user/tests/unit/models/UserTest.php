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
 * UserSettingTest
 *
 * @package humhub.modules_core.user.tests.unit.models
 * @since 0.9
 * @group user

 */
class UserTest extends CDbTestCase
{

    public $fixtures = array(':user', ':group', ':space', ':space_membership');

    protected function setUp()
    {
        parent::setUp();

        Yii::app()->cache->flush();
        RuntimeCache::$data = array();
    }

    public function testIsCurrentUser() {
        Yii::app()->user->id = 1;

        $user = User::model()->findByPk(1);
        $this->assertTrue($user->isCurrentUser());
        
        Yii::app()->user->id = 2;
        $this->assertFalse($user->isCurrentUser());
    }
    
    
    public function testCreateApproval()
    {
        HSetting::Set('needApproval', 0, 'authentication_internal');
        $user = new User();
        $user->username = "TestWithoutApproval";
        $user->email = "approveduser@example.com";
        $this->assertTrue($user->save());
        $this->assertEquals($user->status, User::STATUS_ENABLED);

        HSetting::Set('needApproval', 1, 'authentication_internal');
        $user = new User();
        $user->username = "TestWithApproval";
        $user->email = "unapproveduser@example.com";
        $this->assertTrue($user->save());
        $this->assertEquals($user->status, User::STATUS_NEED_APPROVAL);
    }

    /**
     * Tests if user automatically added to the group´s default space
     */
    public function testCreateGroupSpaceAdd()
    {
        HSetting::Set('needApproval', 0, 'authentication_internal');

        $space = Space::model()->findByPk(1);

        $user = new User();
        $user->username = "TestGroup";
        $user->group_id = 1;
        $user->email = "group@example.com";
        $this->assertTrue($user->save());
        $this->assertTrue($space->isMember($user->id));
    }

    public function testInviteToSpace()
    {
        HSetting::Set('needApproval', 0, 'authentication_internal');

        $userInvite = new UserInvite();
        $userInvite->user_originator_id = 1;
        $userInvite->space_invite_id = 2;
        $userInvite->email = "testspaceinvite@example.com";
        $userInvite->source = UserInvite::SOURCE_INVITE;
        $this->assertTrue($userInvite->save());

        $space = Space::model()->findByPk(2);
        $user = new User();
        $user->username = "TestSpaceInvite";
        $user->group_id = 1;
        $user->email = "testspaceinvite@example.com";
        $this->assertTrue($user->save());
        $this->assertTrue($space->isMember($user->id));
    }

    /**
     * Tests spaces which automatically adds new members
     * Fixture Space 3
     */
    public function testAutoAddSpace()
    {
        $space2 = Space::model()->findByPk(2);
        $space3 = Space::model()->findByPk(3);

        $user = new User();
        $user->username = "TestSpaceAutoAdd";
        $user->group_id = 1;
        $user->email = "testautoadd@example.com";

        $this->assertTrue($user->save());

        $this->assertFalse($space2->isMember($user->id)); // not assigned
        $this->assertTrue($space3->isMember($user->id)); // via global assign
    }

    public function testGroupAssignment()
    {

        $group2 = new Group();
        $group2->name = "TestGrp1";
        $group2->description = "test";
        $this->assertTrue($group2->save());

        HSetting::Set('defaultUserGroup', $group2->id, 'authentication_internal');

        $user = new User();
        $user->username = "TestSpaceAutoAdd";
        $user->email = "testautoadd@example.com";
        $this->assertTrue($user->save());
        $this->assertEquals($user->group_id, $group2->id);
    }

    public function testAutoWallCreation()
    {
        $user = new User();
        $user->username = "wallTest";
        $user->email = "wall@example.com";
        $this->assertTrue($user->save());
        
        $this->assertNotNull($user->wall_id);
        $wall = Wall::model()->findByPk($user->wall_id);
        $this->assertNotNull($wall);
        $this->assertNotNull($user->wall);
    }

}
