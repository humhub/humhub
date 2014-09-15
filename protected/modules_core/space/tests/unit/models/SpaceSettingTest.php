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
 * SpaceSettingTest
 * 
 * @package humhub.modules_core.space.tests.unit.models
 * @since 0.9
 * @group space
 */
class SpaceSettingTest extends CDbTestCase
{

    public $fixtures = array(':space_setting');

    protected function setUp()
    {
        parent::setUp();

        Yii::app()->cache->flush();
        RuntimeCache::$data = array();
    }

    public function testGet()
    {
        $this->assertEquals(SpaceSetting::Get(1, 'globalSetting', 'core'), 'xyz');
        $this->assertEquals(SpaceSetting::Get(1, 'globalSetting'), 'xyz');

        $this->assertEquals(SpaceSetting::Get(1, 'moduleSetting', 'someModule'), 'zyx');

        $space = Space::model()->findByPk(1);
        $this->assertEquals($space->getSetting('globalSetting', 'core'), 'xyz');
        $this->assertEquals($space->getSetting('globalSetting'), 'xyz');
        $this->assertEquals($space->getSetting('moduleSetting', 'someModule'), 'zyx');
    }

    public function testSet()
    {
        SpaceSetting::Set(1, 'globalSetting', 'xyz2');
        $this->assertEquals(SpaceSetting::Get(1, 'globalSetting', 'core'), 'xyz2');
    }

}
