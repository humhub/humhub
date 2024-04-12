<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\models;

use humhub\models\Setting;
use humhub\tests\codeception\unit\components\SettingActiveRecordTest;
use yii\base\Exception;

class SettingTest extends SettingActiveRecordTest
{
    protected $fixtureConfig = ['default'];

    public function testGetCacheKeyFormat()
    {
        /** @noinspection PhpClassConstantAccessedViaChildClassInspection */
        $this->assertEquals('settings-%s', Setting::CACHE_KEY_FORMAT, "Cache key format changed!");
    }

    public function testGetCacheKeyFields()
    {
        /** @noinspection PhpClassConstantAccessedViaChildClassInspection */
        $this->assertEquals(['module_id'], Setting::CACHE_KEY_FIELDS, "Cache key format changed!");
    }

    public function testDeleteAll()
    {
        $settingBefore = Setting::findAll(['module_id' => 'base', 'name' => 'testSetting']);
        $this->assertNotEmpty($settingBefore, "Setting 'testSetting' for 'base' not found.");

        Setting::deleteAll(['module_id' => 'base', 'name' => 'testSetting']);

        $settingAfter = Setting::findAll(['module_id' => 'base', 'name' => 'testSetting']);
        $this->assertCount(0, $settingAfter, "Setting 'testSetting' for 'base' was not deleted.");
    }

    public function testDeprecatedFixModuleIdAndName()
    {
        $this->assertEquals(['foo', 'bar'], Setting::fixModuleIdAndName('foo', 'bar'), "Translation messed things up!");

        $this->assertEquals(
            ['allowGuestAccess', 'user'],
            Setting::fixModuleIdAndName('allowGuestAccess', 'authentication_internal'),
            "Translation messed things up!",
        );
    }

    public function testDeprecatedGetValidSetting()
    {
        $this->assertEquals('Test Setting for Base', Setting::get('testSetting', 'base'), "Invalid value returned!");
    }

    public function testDeprecatedGetSettingFromInvalidModule()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Could not find module: this module does not exist');

        Setting::get('testSetting', 'this module does not exist');
    }

    public function testDeprecatedGetInvalidSetting()
    {
        $this->assertNull(Setting::get('testSetting_', 'base'), "Invalid value returned!");
    }
}
