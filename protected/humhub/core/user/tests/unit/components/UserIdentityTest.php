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
 * UserIdentityTest
 *
 * @package humhub.modules_core.user.tests.unit.components
 * @since 0.9
 * @group user

 */
class UserIdentityTest extends CDbTestCase
{

    public $fixtures = array(':user', ':user_password');

    public function testAuthenticate()
    {
        $identity = new UserIdentity('WrongUser', 'wrongPassword');
        $this->assertFalse($identity->authenticate());
        $this->assertEquals($identity->errorCode, UserIdentity::ERROR_USERNAME_INVALID);

        $identity = new UserIdentity('User1', 'wrongPassword');
        $this->assertFalse($identity->authenticate());
        $this->assertEquals($identity->errorCode, UserIdentity::ERROR_PASSWORD_INVALID);

        $identity = new UserIdentity('User1', '123qwe');
        $this->assertTrue($identity->authenticate());
        $this->assertEquals($identity->errorCode, UserIdentity::ERROR_NONE);

        $identity = new UserIdentity('user1@example.com', 'wrongPassword');
        $this->assertFalse($identity->authenticate());
        $this->assertEquals($identity->errorCode, UserIdentity::ERROR_PASSWORD_INVALID);

        $identity = new UserIdentity('user1@example.com', '123qwe');
        $this->assertTrue($identity->authenticate());
        $this->assertEquals($identity->errorCode, UserIdentity::ERROR_NONE);

        $identity = new UserIdentity('user1@example.com', '');
        $this->assertFalse($identity->authenticate());
        $this->assertEquals($identity->errorCode, UserIdentity::ERROR_PASSWORD_INVALID);

        $user = User::model()->findByPk(1);
        $user->status = User::STATUS_DISABLED;
        $user->save();
        $identity = new UserIdentity('user1@example.com', '123qwe');
        $this->assertFalse($identity->authenticate());
        $this->assertEquals($identity->errorCode, UserIdentity::ERROR_SUSPENDED);

        $user = User::model()->findByPk(1);
        $user->status = User::STATUS_NEED_APPROVAL;
        $user->save();
        $identity = new UserIdentity('user1@example.com', '123qwe');
        $this->assertFalse($identity->authenticate());
        $this->assertEquals($identity->errorCode, UserIdentity::ERROR_NOT_APPROVED);
    }

}
