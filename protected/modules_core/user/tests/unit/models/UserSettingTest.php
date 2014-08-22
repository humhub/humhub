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
class UserSettingTest extends CDbTestCase
{

    public $fixtures = array(':user', ':user_setting');

    protected function setUp()
    {
        parent::setUp();

        Yii::app()->cache->flush();
        RuntimeCache::$data = array();
    }

    public function testGet()
    {
        $this->assertEquals(UserSetting::Get(1, 'globalSetting', 'core'), 'abc');
        $this->assertEquals(UserSetting::Get(1, 'globalSetting'), 'abc');

        $this->assertEquals(UserSetting::Get(1, 'moduleSetting', 'someModule'), 'cba');

        $user = User::model()->findByPk(1);
        $this->assertEquals($user->getSetting('globalSetting', 'core'), 'abc');
        $this->assertEquals($user->getSetting('globalSetting'), 'abc');
        $this->assertEquals($user->getSetting('moduleSetting', 'someModule'), 'cba');
    }

    public function testSet()
    {
        UserSetting::Set(1, 'globalSetting', 'abc2');
        $this->assertEquals(UserSetting::Get(1, 'globalSetting', 'core'), 'abc2');
    }

}
