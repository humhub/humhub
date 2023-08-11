<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components;

use humhub\interfaces\FindInstanceInterface;
use humhub\modules\user\tests\codeception\fixtures\UserFullFixture;
use humhub\tests\codeception\fixtures\SettingFixture;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * @since 1.15
 */
class RuntimeCacheWithDbTest extends HumHubDbTestCase
{
    protected $fixtureConfig = [
        'user' => UserFullFixture::class,
        'settings' => SettingFixture::class,
    ];

    protected const REGEX_VALID = '/^.*$/';
    protected const REGEX_INVALID = '^.*$/';
    protected const REGEX_VALID_SUBPATTERN = '/^\\\\(.*)$/';

    public function testCacheActiveRecord()
    {
        $cache = new RuntimeArrayCacheMock();

        RuntimeCacheActiveRecordMock::$tableName = 'setting';
        $record = RuntimeCacheActiveRecordMock::findOne(['id' => 1]);

        static::assertInstanceOf(RuntimeCacheActiveRecordMock::class, $record);

        $key = 'test1';
        $hash = 'a004c99262cb62a096b1dfb09b441af0';
        $uniqueId = 'humhub_tests_codeception_unit_components_RuntimeCacheActiveRecordMock__1';

        static::assertTrue($cache->set($key, $record));
        static::assertEquals([$key, $hash], $cache->lastKey);
        $store = $cache->getAll();
        static::assertEquals([$key => $record, $uniqueId => $record], $store);

        static::assertEquals($record, $cache->delete($key));
        static::assertEquals([], $cache->getCache()->getArrayCopy());
        static::assertEquals([], $cache->getAll());

        $cache = new RuntimeArrayCacheMock();

        static::assertTrue($cache->set(null, $record));
        static::assertEquals([$hash, $hash], $cache->lastKey);
        $store = $cache->getAll();
        static::assertEquals([$uniqueId => $record], $store);

        static::assertEquals([$uniqueId => $record], $cache->delete($record));
        static::assertEquals([], $cache->getCache()->getArrayCopy());

        RuntimeCacheActiveRecordMock::$tableName = 'user';
        $record = RuntimeCacheActiveRecordMock::findOne(['id' => 2]);
        static::assertInstanceOf(RuntimeCacheActiveRecordMock::class, $record);

        $uniqueId1 = 'humhub_tests_codeception_unit_components_RuntimeCacheActiveRecordMock__2';
        $hash1 = 'b0ab28146771bf65a2255a1d6577a761';
        $uniqueId2 = 'humhub_tests_codeception_unit_components_RuntimeCacheActiveRecordMock__01e50e0d-82cd-41fc-8b0c-552392f5839d';
        $hash2 = '5f78d1bbb32a258fdbbfc443811d64bc';

        $cache = new RuntimeArrayCacheMock();

        static::assertTrue($cache->set(null, $record));
        static::assertEquals([$hash1, $hash2, $hash1, $hash2], $cache->lastKey);
        $store = $cache->getAll();
        static::assertEquals([$uniqueId1 => $record, $uniqueId2 => $record], $store);

        static::assertEquals([$uniqueId1 => $record, $uniqueId2 => $record], $cache->delete($record));
        static::assertEquals([], $cache->getCache()->getArrayCopy());
    }

    public function testCacheClearing()
    {
        $cache = new RuntimeArrayCacheMock();

        static::assertTrue($cache->set('test', null));
        static::assertTrue($cache->set('test1', null));
        static::assertTrue($cache->set('test1_1', null));
        static::assertTrue($cache->set('test2', null));
        static::assertTrue($cache->set('test11', null));

        static::assertEquals(['test' => null, 'test1' => null, 'test1_1' => null, 'test2' => null, 'test11' => null], $cache->getAll());

        RuntimeCacheActiveRecordMock::$tableName = 'setting';
        $record = RuntimeCacheActiveRecordMock::findOne(['id' => 1]);
        static::assertInstanceOf(RuntimeCacheActiveRecordMock::class, $record);

        $uniqueId = 'humhub_tests_codeception_unit_components_RuntimeCacheActiveRecordMock__1';

        static::assertTrue($cache->set('test1_1', $record));
        static::assertEquals(['test' => null, 'test1' => null, 'test2' => null, 'test11' => null, 'test1_1' => $record, $uniqueId => $record], $cache->getAll());

        static::assertEquals([ 'test1_1' => $record, 'test1' => null], $cache->flush('test1'));
        static::assertEquals(['test' => null, 'test2' => null, 'test11' => null], $cache->getAll());

        static::assertTrue($cache->set('test1_1', $record));
        static::assertEquals(['test' => null, 'test2' => null, 'test11' => null, 'test1_1' => $record, $uniqueId => $record], $cache->getAll());

        static::assertEquals([$uniqueId => $record, 'test1_1' => $record], $cache->flush(RuntimeCacheActiveRecordMock::class));
        static::assertEquals(['test' => null, 'test2' => null, 'test11' => null], $cache->getAll());

        static::assertTrue($cache->set('test1_1', $record));
        static::assertEquals(['test' => null, 'test2' => null, 'test11' => null, 'test1_1' => $record, $uniqueId => $record], $cache->getAll());

        static::assertEquals([$uniqueId => $record, 'test1_1' => $record], $cache->flush(RuntimeCacheActiveRecordMock::class));
        static::assertEquals(['test' => null, 'test2' => null, 'test11' => null], $cache->getAll());
    }


    public function testFindInstanceCache()
    {
        $currentCache = Yii::$app->runtimeCache;
        Yii::$app->set('runtimeCache', $cache = new FindInstanceCache(['serializer' => false]));
        static::assertNull($cache->cacheRead);
        static::assertNull($cache->cacheWritten);
        static::assertFalse($cache->valueRetrieved);

        // get non-cached object
        static::assertInstanceOf(FindInstanceInterface::class, $instance = FindInstanceMock::findInstance(1));
        static::assertNotNull($cache->cacheRead);
        static::assertNotNull($cache->cacheWritten);
        static::assertEquals($instance, $cache->valueRetrieved);

        $cache->resetState();

        // get the same object from cache
        static::assertInstanceOf(FindInstanceInterface::class, $instance2 = FindInstanceMock::findInstance(1));
        static::assertNotNull($cache->cacheRead);
        static::assertNull($cache->cacheWritten);
        static::assertFalse($cache->valueRetrieved);
        static::assertEquals($instance, $cache->get('humhub_tests_codeception_unit_components_FindInstanceMock__1'));
        static::assertEquals(spl_object_id($instance2), spl_object_id($cache->get('humhub_tests_codeception_unit_components_FindInstanceMock__1')));
        static::assertEquals(spl_object_id($instance), spl_object_id($cache->get('humhub_tests_codeception_unit_components_FindInstanceMock__1')));

        $cache->resetState();

        // get an un-cached object (new object from "db") and store it in cache
        static::assertInstanceOf(FindInstanceInterface::class, $instance2 = FindInstanceMock::findInstance(1, ['cached' => false]));
        static::assertNull($cache->cacheRead);
        static::assertNotNull($cache->cacheWritten);
        static::assertFalse($cache->valueRetrieved);

        // check it's like the object from the cache
        static::assertEquals($instance2, $cache->get('humhub_tests_codeception_unit_components_FindInstanceMock__1'));

        // check it *is* the object from the cache
        static::assertEquals(spl_object_id($instance2), spl_object_id($cache->get('humhub_tests_codeception_unit_components_FindInstanceMock__1')));

        // check if it looks like our original object
        static::assertEquals($instance2, $instance);

        // check that it is however *not* the same object
        static::assertNotEquals(spl_object_id($instance), spl_object_id($instance2));

        // restore original cache
        Yii::$app->set('runtimeCache', $currentCache);
    }
}
