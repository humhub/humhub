<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

/** @noinspection MissedFieldInspection */

namespace humhub\tests\codeception\unit\components;

use humhub\components\SettingsManager;
use humhub\libs\BaseSettingsManager;
use humhub\models\Setting;
use humhub\modules\content\components\ContentContainerSettingsManager;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\caching\ArrayCache;
use yii\caching\DummyCache;
use yii\helpers\ArrayHelper;

use function PHPUnit\Framework\assertInstanceOf;

class SettingsManagerTest extends HumHubDbTestCase
{
    protected $fixtureConfig = ['default'];

    public function testCreateWithoutModuleId()
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Module id not set!');

        new SettingsManager();
    }

    public function testCreateWithEmptyModuleId()
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Empty module id!');

        $this->s = new SettingsManager(['moduleId' => '']);
    }

    public function testCreateForBaseModule()
    {
        $module = 'base';
        $sm = new SettingsManager(['moduleId' => $module]);

        $this->assertInstanceOf(SettingsManager::class, $sm);
    }

    public function testCreateForNonExistentModule()
    {
        $sm = new SettingsManager(['moduleId' => '_']);

        $this->assertInstanceOf(SettingsManager::class, $sm);
    }

    public function testGetValuesForBaseModule()
    {
        $module = 'base';
        $sm = new SettingsManager(['moduleId' => $module]);

        $value = $sm->get('testSetting');
        $this->assertEquals('Test Setting for Base', $value);

        $value = $sm->get('testSetting_');
        $this->assertNull($value);
    }

    public function testSpace()
    {
        $module = 'base';
        $sm = new SettingsManager(['moduleId' => $module]);

        $smSpace = $sm->space();
        $this->assertNull($smSpace, "No Space Settings Manager should have been returned");

        $space = Space::findOne(['id' => 1]);
        $this->assertInstanceOf(Space::class, $space);

        $smSpace = $sm->space($space);
        $this->assertInstanceOf(
            ContentContainerSettingsManager::class,
            $smSpace,
            "No Space Settings Manager was returned",
        );
        $this->assertEquals($module, $smSpace->moduleId);
        $this->assertEquals($space, $smSpace->contentContainer);
    }

    public function testUser()
    {
        $module = 'base';
        $sm = new SettingsManager(['moduleId' => $module]);

        $smUser = $sm->user();
        $this->assertNull($smUser, "No User Settings Manager should have been returned");

        $user = $this->becomeUser('User2');

        $smUser = $sm->user();
        $this->assertInstanceOf(
            ContentContainerSettingsManager::class,
            $smUser,
            "No User Settings Manager was returned",
        );
        $this->assertEquals($module, $smUser->moduleId);
        $this->assertEquals($user, $smUser->contentContainer);

        $user = User::findOne(['id' => 2]);
        $this->assertInstanceOf(User::class, $user);

        $smUser = $sm->user($user);
        $this->assertInstanceOf(
            ContentContainerSettingsManager::class,
            $smUser,
            "No User Settings Manager was returned",
        );
        $this->assertEquals($module, $smUser->moduleId);
        $this->assertEquals($user, $smUser->contentContainer);
    }

    public function testSettingValues()
    {
        $module = 'base';
        $table = Setting::tableName();
        $sm = new SettingsManager(['moduleId' => $module]);

        $this->assertEquals('Test Setting for Base', $sm->get('testSetting'));

        $setting = 'testSetting';
        $value = 'Hello World';

        $sm->set($setting, $value);
        $this->assertRecordExists($table, ['name' => $setting, 'module_id' => $module]);
        $this->assertRecordValue($value, 'value', $table, ['name' => $setting, 'module_id' => $module]);
        $this->assertEquals($value, $sm->get($setting));

        $setting = 'testSetting_';
        $value = 'Brave New World';

        $this->assertRecordNotExists($table, ['name' => $setting, 'module_id' => $module]);
        $this->assertNull($sm->get($setting));

        $sm->set($setting, $value);
        $this->assertRecordExists($table, ['name' => $setting, 'module_id' => $module]);
        $this->assertRecordValue($value, 'value', $table, ['name' => $setting, 'module_id' => $module]);
        $this->assertEquals($value, $sm->get($setting));

        $this->expectExceptionTypeError(BaseSettingsManager::class, 'set', 1, '$name', 'string', 'null');
        $sm->set(null, "NULL");

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('@Argument #1 \\(\\$name\\) passed to .* may not be an empty string!@');
        $sm->set('', "null-length string");

        $setting = ' ';
        $value = 'just a space';

        $sm->set($setting, $value);
        $this->assertRecordExists($table, ['name' => $setting, 'module_id' => $module]);
        $this->assertRecordValue($value, 'value', $table, ['name' => $setting, 'module_id' => $module]);
        $this->assertEquals($value, $sm->get($setting));
    }

    public function testSettingFixedValues()
    {
        $module = 'base';
        $sm = new SettingsManager(['moduleId' => $module]);

        // Test callable function for fixed settings
        Yii::$app->params['fixed-settings'][$module]['test.first'] = function (SettingsManager $sm) {
            if ($sm->get('test.second') === 'secondValueFixed1') {
                return 'value1FromFixedConfig';
            }
            if ($sm->get('test.second') === 'secondValueFixed2') {
                return 'value2FromFixedConfig';
            }
            return null;
        };

        $sm->set('test.first', 'firstValueDB');
        $sm->set('test.second', 'secondValueDB');

        $this->assertEquals($sm->get('test.first'), 'firstValueDB');
        $this->assertEquals($sm->get('test.second'), 'secondValueDB');

        // Set special value for second param in order to force the first param from fixed config
        $sm->set('test.second', 'secondValueFixed1');
        $this->assertEquals($sm->get('test.first'), 'value1FromFixedConfig');

        $sm->set('test.second', 'secondValueFixed2');
        $this->assertEquals($sm->get('test.first'), 'value2FromFixedConfig');

        // Test simple value
        Yii::$app->params['fixed-settings'][$module]['test.first'] = 'staticValueFromFixedConfig';
        $this->assertEquals($sm->get('test.first'), 'staticValueFromFixedConfig');

        // Reset fixed value
        Yii::$app->params['fixed-settings'][$module]['test.first'] = null;
        $this->assertEquals($sm->get('test.first'), 'firstValueDB');
    }

    public function testSerialized()
    {
        $module = 'base';
        $table = Setting::tableName();
        $sm = new SettingsManager(['moduleId' => $module]);

        $setting = 'testJson';
        $this->assertRecordNotExists($table, ['name' => $setting, 'module_id' => $module]);
        $this->assertNull($sm->getSerialized($setting), "Setting should not exist");

        $tests = [
            '5' => 5,
            '"5"' => "5",
            '5.5' => 5.5,
            '"simple text"' => 'simple text',
            'null' => null,
            '[]' => [],
            '{}' => (object)[],
            '[null,"simple text","text with \"quotes\"",5]' => [null, 'simple text', 'text with "quotes"', 5],
            '{"0":null,"x":"simple text"}' => [null, 'x' => 'simple text'],
            '{"x":null,"y":"simple text"}' => (object)['x' => null, 'y' => 'simple text'],
        ];

        array_walk($tests, function ($value, $json) use ($setting, $sm, $table, $module) {
            $sm->setSerialized($setting, $value);
            $this->assertRecordExists($table, ['name' => $setting, 'module_id' => $module]);
            $this->assertRecordValue($json, 'value', $table, ['name' => $setting, 'module_id' => $module]);
            $this->assertEquals($json, $sm->get($setting));

            if (is_object($value) || is_array($value) && ArrayHelper::isAssociative($value)) {
                $object = (object)$value;
                $this->assertEquals($object, $sm->getSerialized($setting, null, false), "testing json: $json");
                $value = (array)$value;
            }
            $this->assertEquals($value, $sm->getSerialized($setting), "testing json: $json");
        });
    }

    public function testDelete()
    {
        $module = 'base';
        $table = Setting::tableName();
        $sm = new SettingsManager(['moduleId' => $module]);

        // delete by null value
        $setting = 'testSetting';
        $this->assertNotNull($sm->get($setting));
        $sm->set($setting, null);
        $this->assertNull($sm->get($setting));

        // delete by delete()
        $setting = 'testSetting0';
        $this->assertRecordExists($table, ['name' => $setting, 'module_id' => $module]);
        $this->assertNotNull($sm->get($setting));
        $sm->delete($setting);
        $this->assertRecordNotExists($table, ['name' => $setting, 'module_id' => $module]);
        $this->assertNull($sm->get($setting));
    }

    public function testDeleteAll()
    {
        $module = 'base';
        $table = Setting::tableName();
        $sm = new SettingsManager(['moduleId' => $module]);

        $setting = 'testSetting';
        $setting1 = 'testSetting_1';
        $setting2 = 'testSetting_2';

        $sm->set($setting1, 'something');
        $sm->set($setting2, 'something');

        $this->assertNotNull($sm->get($setting), "testSetting should not found");
        $this->assertNotNull($sm->get($setting1), "testSetting_1 was not created");
        $this->assertNotNull($sm->get($setting2), "testSetting_1 was not created");
        $this->assertRecordExists($table, ['name' => $setting, 'module_id' => $module]);
        $this->assertRecordExists($table, ['name' => $setting1, 'module_id' => $module]);
        $this->assertRecordExists($table, ['name' => $setting2, 'module_id' => $module]);

        $sm->deleteAll('Setting_');

        $this->assertNotNull($sm->get($setting), "testSetting should not have been deleted");
        $this->assertNotNull($sm->get($setting1), "testSetting_1 should not have been deleted");
        $this->assertNotNull($sm->get($setting2), "testSetting_2 should not have been deleted");
        $this->assertRecordExists($table, ['name' => $setting, 'module_id' => $module]);
        $this->assertRecordExists($table, ['name' => $setting1, 'module_id' => $module]);
        $this->assertRecordExists($table, ['name' => $setting2, 'module_id' => $module]);

        $sm->deleteAll('testSetting_');

        $this->assertNotNull($sm->get($setting), "testSetting should not have been deleted");
        $this->assertNull($sm->get($setting1), "testSetting_1 should have been deleted");
        $this->assertNull($sm->get($setting2), "testSetting_2 should have been deleted");
        $this->assertRecordExists($table, ['name' => $setting, 'module_id' => $module]);
        $this->assertRecordNotExists($table, ['name' => $setting1, 'module_id' => $module]);
        $this->assertRecordNotExists($table, ['name' => $setting2, 'module_id' => $module]);

        $sm->deleteAll('%Setting%');

        $this->assertNull($sm->get($setting), "testSetting should not have been deleted");
        $this->assertRecordNotExists($table, ['name' => $setting, 'module_id' => $module]);

        $this->assertRecordExistsAny($table, ['module_id' => $module]);
        $sm->deleteAll();
        $this->assertRecordNotExists($table, ['module_id' => $module]);
    }

    public function testGetCached()
    {
        $module = 'base';
        $table = Setting::tableName();

        // make sure, cache is disabled

        $cache = Yii::$app->cache;

        if (!$cache instanceof DummyCache) {
            Yii::$app->set('cache', new DummyCache());
        }

        assertInstanceOf(DummyCache::class, $cache = Yii::$app->cache);

        // No Cache

        // initialize and load data from database
        $sm = new SettingsManagerMock(['moduleId' => $module]);
        $this->assertTrue($sm->didAccessDB());

        // do it again. If there is a cache, then the database is not used. Since there is no cache, it is used again
        $sm = new SettingsManagerMock(['moduleId' => $module]);
        $this->assertTrue($sm->didAccessDB());

        // Enable Cache
        Yii::$app->set('cache', new ArrayCache());
        assertInstanceOf(ArrayCache::class, $cache = Yii::$app->cache);

        // Enabled Cache

        // initialize and load data from database
        $sm = new SettingsManagerMock(['moduleId' => $module]);
        $this->assertTrue($sm->didAccessDB());

        // check if cache is saved
        $key = $sm->getCacheKey();
        $this->assertTrue($cache->exists($key));

        // do it again. If there is a cache, then the database is not used. Since there is no cache, it is used again
        $sm = new SettingsManagerMock(['moduleId' => $module]);
        $this->assertFalse($sm->didAccessDB());

        $key = $sm->getCacheKey();
        $this->assertTrue($cache->exists($key));

        $setting = 'testSetting';

        // reading a value should not access the db
        $this->assertNotNull($sm->get($setting));
        $this->assertFalse($sm->didAccessDB());

        // however, writing a value should both
        $value = 'some other value';
        $sm->set($setting, $value);
        // ... access the db
        $this->assertTrue($sm->didAccessDB());
        // ... and clear the cache
        $this->assertFalse($cache->exists($key));
        // DB should be updated, too
        $this->assertRecordValue($value, 'value', $table, ['name' => $setting, 'module_id' => $module]);

        // next read should still
        $this->assertEquals($value, $sm->get($setting));
        // ... not access db
        $this->assertFalse($sm->didAccessDB());
        // ... or create the cache
        $this->assertFalse($cache->exists($key));

        // changing the value behind the scenes
        $value2 = 'third value';
        self::dbUpdate($table, ['value' => $value2], ['name' => $setting, 'module_id' => $module]);
        $this->assertRecordValue($value2, 'value', $table, ['name' => $setting, 'module_id' => $module]);

        // getting the value now should still show tho "old" value
        $this->assertEquals($value, $sm->get($setting));

        // reloading the settings should
        $sm->reload();
        // ... access the db
        $this->assertTrue($sm->didAccessDB());
        // ... and re-build the cache
        $this->assertTrue($cache->exists($key));
        // ... and show the updated value
        $this->assertEquals($value2, $sm->get($setting));

        // invalidating the cache
        $sm->invalidateCache();
        // ... not access db
        $this->assertFalse($sm->didAccessDB());
        // ... but clear the cache
        $this->assertFalse($cache->exists($key));
    }
}
