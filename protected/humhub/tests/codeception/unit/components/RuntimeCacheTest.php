<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components;

use humhub\components\RuntimeArrayCache;
use humhub\exceptions\InvalidArgumentException;
use humhub\exceptions\InvalidArgumentTypeException;
use humhub\interfaces\FindInstanceInterface;
use humhub\interfaces\RuntimeCacheInterface;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * @since 1.15
 */
class RuntimeCacheTest extends HumHubDbTestCase
{
    protected const REGEX_VALID = '/^.*$/';
    protected const REGEX_INVALID = '^.*$/';
    protected const REGEX_VALID_SUBPATTERN = '/^\\\\(.*)$/';

    public function testInstantiation()
    {
        $cache = new RuntimeArrayCache();

        static::assertInstanceOf(RuntimeArrayCache::class, $cache);
        static::assertInstanceOf(RuntimeCacheInterface::class, $cache);
    }

    public function testRegexValidation()
    {
        $cache = new RuntimeArrayCacheMock();

        static::assertTrue($cache->checkRegex(static::REGEX_VALID, false));
        static::assertFalse($cache->checkRegex(static::REGEX_INVALID, false));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Argument #1 $regex passed to humhub\components\RuntimeBaseCache::checkRegex must be one of string, string[] - "string" must be a valid regex pattern (see https://www.php.net/manual/en/pcre.pattern.php). Error: Internal error - %s given.', static::REGEX_INVALID));
        static::assertFalse($cache->checkRegex(static::REGEX_INVALID));
    }

    public function testCacheBasics()
    {
        $cache = new RuntimeArrayCacheMock();
        static::assertNull($cache->cacheRead);
        static::assertNull($cache->cacheWritten);
        static::assertFalse($cache->valueRetrieved);
        static::assertEquals(0, $cache->getDistinctCount());

        // test read from empty cache

        static::assertFalse($cache->get('test'));
        static::assertEquals(['test'], $cache->cacheRead);
        static::assertNull($cache->cacheWritten);
        static::assertFalse($cache->valueRetrieved);

        static::assertEquals([], $cache->getAll());

        $cache->resetState();

        // test write of simple value (default value of null)

        static::assertTrue($cache->set('test'));
        static::assertNull($cache->cacheRead);
        static::assertEquals([['test' => 'test']], $cache->cacheWritten);
        static::assertFalse($cache->valueRetrieved);

        static::assertEquals(['test' => null], $cache->getAll());
        static::assertEquals(1, $cache->getDistinctCount());

        $cache->resetState();

        // test reading value

        static::assertEquals(null, $cache->get('test'));
        static::assertEquals(['test'], $cache->cacheRead);
        static::assertNull($cache->cacheWritten);
        static::assertFalse($cache->valueRetrieved);

        $cache->resetState();

        // test updating value

        static::assertTrue($cache->set('test', 1));
        static::assertNull($cache->cacheRead);
        static::assertEquals([['test' => 'test']], $cache->cacheWritten);
        static::assertFalse($cache->valueRetrieved);

        static::assertEquals(['test' => 1], $cache->getAll());
        static::assertEquals(1, $cache->getDistinctCount());

        $cache->resetState();

        // writing with multiple keys as aliases

        static::assertTrue($cache->set(['test1_1', 'test1_2'], '1_1 & 1_2'));
        static::assertNull($cache->cacheRead);
        static::assertEquals([['test1_1' => 'test1_1', 'test1_2' => 'test1_2']], $cache->cacheWritten);
        static::assertFalse($cache->valueRetrieved);

        static::assertEquals(['test' => 1, 'test1_1' => '1_1 & 1_2', 'test1_2' => '1_1 & 1_2'], $cache->getAll());
        static::assertEquals(2, $cache->getDistinctCount());

        $cache->resetState();

        // updating one of the two linked values

        static::assertTrue($cache->set('test1_1', '1_1 AND 1_2'));
        static::assertNull($cache->cacheRead);
        static::assertEquals([['test1_1' => 'test1_1']], $cache->cacheWritten);
        static::assertFalse($cache->valueRetrieved);

        static::assertEquals(['test' => 1, 'test1_1' => '1_1 AND 1_2', 'test1_2' => '1_1 AND 1_2'], $cache->getAll());
        static::assertEquals(2, $cache->getDistinctCount());

        $cache->resetState();

        // adding two key->value pairs

        static::assertTrue($cache->set(['test2_1' => '2_1', 'test2_2' => '2_2'], 'ignored'));
        static::assertNull($cache->cacheRead);
        static::assertEquals([['test2_1' => 'test2_1'], ['test2_2' => 'test2_2']], $cache->cacheWritten);
        static::assertFalse($cache->valueRetrieved);

        static::assertEquals(['test' => 1, 'test1_1' => '1_1 AND 1_2', 'test1_2' => '1_1 AND 1_2', 'test2_1' => '2_1', 'test2_2' => '2_2'], $cache->getAll());
        static::assertEquals(4, $cache->getDistinctCount());

        $cache->resetState();

        // deleting a simple value

        static::assertEquals('2_1', $cache->delete('test2_1'));
        static::assertNull($cache->cacheRead);
        static::assertNull($cache->cacheWritten);
        static::assertFalse($cache->valueRetrieved);

        static::assertEquals(['test' => 1, 'test1_1' => '1_1 AND 1_2', 'test1_2' => '1_1 AND 1_2', 'test2_2' => '2_2'], $cache->getAll());
        static::assertEquals(3, $cache->getDistinctCount());

        $cache->resetState();

        // deleting a linked value

        static::assertEquals('1_1 AND 1_2', $cache->delete('test1_1'));
        static::assertNull($cache->cacheRead);
        static::assertNull($cache->cacheWritten);
        static::assertFalse($cache->valueRetrieved);

        static::assertEquals(['test' => 1, 'test2_2' => '2_2'], $cache->getAll());
        static::assertEquals(2, $cache->getDistinctCount());

        $cache->resetState();

        // copying a simple value to a non-existing key

        static::assertNull($cache->link('test', 'test_copy'));
        static::assertNull($cache->cacheRead);
        static::assertNull($cache->cacheWritten);
        static::assertFalse($cache->valueRetrieved);

        static::assertEquals(['test' => 1, 'test_copy' => 1, 'test2_2' => '2_2'], $cache->getAll());
        static::assertEquals(2, $cache->getDistinctCount());

        $cache->resetState();

        // copying a simple value to a single-key entry

        static::assertNull($cache->link('test', 'test2_2'));
        static::assertNull($cache->cacheRead);
        static::assertNull($cache->cacheWritten);
        static::assertFalse($cache->valueRetrieved);

        static::assertEquals(['test' => 1, 'test_copy' => 1, 'test2_2' => '1'], $cache->getAll());
        static::assertEquals(1, $cache->getDistinctCount());

        $cache->resetState();

        // unlink key

        static::assertEquals(['test_copy' => 1, 'test2_2' => 1, 'test3' => null], $cache->unlink('test_copy', 'test2_2', 'test3'));
        static::assertNull($cache->cacheRead);
        static::assertNull($cache->cacheWritten);
        static::assertFalse($cache->valueRetrieved);

        static::assertEquals(['test' => 1], $cache->getAll());
        static::assertEquals(1, $cache->getDistinctCount());

        $cache->resetState();
    }

    public function testCacheAndFindInstance()
    {
        $currentCache = Yii::$app->runtimeCache;
        Yii::$app->set('runtimeCache', $cache = new RuntimeArrayCacheMock(['serializer' => false]));
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

    public function testCacheSorting()
    {
        $cache = new RuntimeArrayCacheMock();

        // freeze time
        $cache->setPointOfTime(-1);

        $cache->set(['test' => 0, 'test1' => 1]);
        $cache->set('test2', 2);

        // defrost time
        $cache->setPointOfTime(null);

        $all = $cache->getAll();
        static::assertEquals(['test' => 0, 'test1' => 1, 'test2' => 2], $all);
        static::assertEquals(['test', 'test1', 'test2'], array_keys($all));

        $cache->sort();

        $all = $cache->getAll();
        static::assertEquals(['test' => 0, 'test1' => 1, 'test2' => 2], $all);
        static::assertEquals(['test', 'test1', 'test2'], array_keys($all));

        $cache = new RuntimeArrayCacheMock();

        static::assertTrue($cache->set('test', 0));
        usleep(200000);
        static::assertTrue($cache->set('test1', 1));
        usleep(200000);
        static::assertTrue($cache->set('test2', 2));

        $all = $cache->getAll();
        static::assertEquals(['test' => 0, 'test1' => 1, 'test2' => 2], $all);
        static::assertEquals(['test', 'test1', 'test2'], array_keys($all));

        $cache->sort();

        $all = $cache->getAll();
        static::assertEquals(['test2' => 2, 'test1' => 1, 'test' => 0], $all);
        static::assertEquals(['test2', 'test1', 'test'], array_keys($all));

        // make a few reeds on test
        static::assertEquals(0, $cache->get('test'));
        static::assertEquals(0, $cache->get('test'));

        $cache->sort();

        $all = $cache->getAll();
        static::assertEquals(['test2' => 2, 'test1' => 1, 'test' => 0], $all);
        static::assertEquals(['test', 'test2', 'test1'], array_keys($all));

        static::assertEquals(1, $cache->get('test1'));

        $cache->sort();

        $all = $cache->getAll();
        static::assertEquals(['test2' => 2, 'test1' => 1, 'test' => 0], $all);
        static::assertEquals(['test', 'test1', 'test2'], array_keys($all));

        static::assertEquals(1, $cache->get('test1'));

        $cache->sort();

        $all = $cache->getAll();
        static::assertEquals(['test2' => 2, 'test1' => 1, 'test' => 0], $all);
        static::assertEquals(['test1', 'test', 'test2'], array_keys($all));
    }

    public function testManageIncludes()
    {
        // simple instantiation
        $cache = new RuntimeArrayCache();
        static::assertNull($cache->getInclude());

        // instantiation with null includes
        $cache = new RuntimeArrayCache(['include' => null]);
        static::assertNull($cache->getInclude());

        // instantiation with empty includes
        $cache = new RuntimeArrayCache(['include' => []]);
        static::assertNull($cache->getInclude());

        // instantiation with valid includes
        $cache = new RuntimeArrayCache(['include' => [static::REGEX_VALID]]);
        static::assertEquals([static::REGEX_VALID], $cache->getInclude());

        // adding a new pattern
        $cache->addInclusion(self::REGEX_VALID_SUBPATTERN);
        static::assertEquals([static::REGEX_VALID, self::REGEX_VALID_SUBPATTERN], $cache->getInclude());

        // replace all patterns
        $cache->setInclude([static::REGEX_VALID, self::REGEX_VALID_SUBPATTERN]);
        static::assertEquals([static::REGEX_VALID, self::REGEX_VALID_SUBPATTERN], $cache->getInclude());

        // adding existing pattern
        $cache->addInclusion(self::REGEX_VALID);
        static::assertEquals([static::REGEX_VALID, self::REGEX_VALID_SUBPATTERN], $cache->getInclude());

        // remove an existing pattern
        $cache->removeInclusion(self::REGEX_VALID);
        static::assertEquals([1 => self::REGEX_VALID_SUBPATTERN], $cache->getInclude());

        // remove a non-existing pattern
        $cache->removeInclusion(self::REGEX_VALID);
        static::assertEquals([1 => self::REGEX_VALID_SUBPATTERN], $cache->getInclude());

        // remove a pattern by index
        $cache->removeInclusion(1);
        static::assertNull($cache->getInclude());

        // remove a pattern by a non-existing index
        $cache->removeInclusion(1);
        static::assertNull($cache->getInclude());

        $this->expectException(InvalidArgumentTypeException::class);
        $this->expectExceptionMessage('Argument #1 $inclusion passed to humhub\components\RuntimeBaseCache::removeInclusion must be of type string, int - humhub\components\RuntimeArrayCache given.');

        /** @noinspection PhpParamsInspection */
        $cache->removeInclusion($cache);
    }

    public function testManageExcludes()
    {
        // simple instantiation
        $cache = new RuntimeArrayCache();
        static::assertNull($cache->getExclude());

        // instantiation with null excludes
        $cache = new RuntimeArrayCache(['exclude' => null]);
        static::assertNull($cache->getExclude());

        // instantiation with empty excludes
        $cache = new RuntimeArrayCache(['exclude' => []]);
        static::assertNull($cache->getExclude());

        // instantiation with valid excludes
        $cache = new RuntimeArrayCache(['exclude' => [static::REGEX_VALID]]);
        static::assertEquals([static::REGEX_VALID], $cache->getExclude());

        // adding a new pattern
        $cache->addExclusion(self::REGEX_VALID_SUBPATTERN);
        static::assertEquals([static::REGEX_VALID, self::REGEX_VALID_SUBPATTERN], $cache->getExclude());

        // replace all patterns
        $cache->setExclude([self::REGEX_VALID_SUBPATTERN, static::REGEX_VALID]);
        static::assertEquals([self::REGEX_VALID_SUBPATTERN, static::REGEX_VALID], $cache->getExclude());

        // adding existing pattern
        $cache->addExclusion(self::REGEX_VALID);
        static::assertEquals([self::REGEX_VALID_SUBPATTERN, static::REGEX_VALID], $cache->getExclude());

        // remove an existing pattern
        $cache->removeExclusion(self::REGEX_VALID_SUBPATTERN);
        static::assertEquals([1 => self::REGEX_VALID], $cache->getExclude());

        // remove a non-existing pattern
        $cache->removeExclusion(self::REGEX_VALID_SUBPATTERN);
        static::assertEquals([1 => self::REGEX_VALID], $cache->getExclude());

        // remove a pattern by index
        $cache->removeExclusion(1);
        static::assertNull($cache->getExclude());

        // remove a pattern by a non-existing index
        $cache->removeExclusion(1);
        static::assertNull($cache->getExclude());

        $this->expectException(InvalidArgumentTypeException::class);
        $this->expectExceptionMessage('Argument #1 $exclusion passed to humhub\components\RuntimeBaseCache::removeExclusion must be of type string, int - humhub\components\RuntimeArrayCache given.');

        $cache->removeExclusion($cache);
    }

    public function testCacheIncludeAndExcludeRules()
    {
        $cache = new RuntimeArrayCacheMock(['include' => '/^test1/']);

        static::assertFalse($cache->set('test', null));
        static::assertTrue($cache->set('test1', null));
        static::assertFalse($cache->set('test2', null));

        $cache = new RuntimeArrayCacheMock(['exclude' => '/^test1/']);

        static::assertTrue($cache->set('test', null));
        static::assertFalse($cache->set('test1', null));
        static::assertTrue($cache->set('test2', null));

        $cache = new RuntimeArrayCacheMock(['include' => '/^test1_2/', 'exclude' => '/^test1/']);

        static::assertTrue($cache->set('test', null));
        static::assertFalse($cache->set('test1', null));
        static::assertTrue($cache->set('test1_2', null));
        static::assertTrue($cache->set('test2', null));

        static::assertEquals(['test' => null, 'test2' => null, 'test1_2' => null], $cache->getAll());
        static::assertEquals(3, $cache->getDistinctCount());

        $cache->removeInclusion('/^test1_2/');

        static::assertEquals(['test' => null, 'test2' => null], $cache->getAll());
        static::assertEquals(2, $cache->getDistinctCount());

        // adding another entry
        static::assertTrue($cache->set('foo', 'Foo'));
        static::assertEquals(['test' => null, 'test2' => null, 'foo' => 'Foo'], $cache->getAll());
        static::assertEquals(3, $cache->getDistinctCount());

        // adding an entry with multiple keys, one of which is excluded
        static::assertFalse($cache->set(['bar', 'test1'], 'Bar'));
        static::assertEquals(['test' => null, 'test2' => null, 'foo' => 'Foo'], $cache->getAll());
        static::assertEquals(3, $cache->getDistinctCount());

        // adding an entry with multiple keys, one of which is excluded
        static::assertTrue($cache->set(['bar', 'testX'], 'Bar'));
        static::assertEquals(['test' => null, 'test2' => null, 'foo' => 'Foo', 'bar' => 'Bar', 'testX' => 'Bar'], $cache->getAll());
        static::assertEquals(4, $cache->getDistinctCount());


        $cache->addInclusion('/^testX$/');
        $cache->setExclude('/^test/');

        static::assertEquals(['foo' => 'Foo', 'bar' => 'Bar', 'testX' => 'Bar'], $cache->getAll());
        static::assertEquals(2, $cache->getDistinctCount());

        $cache->setInclude(null);

        static::assertEquals(['foo' => 'Foo'], $cache->getAll());
        static::assertEquals(1, $cache->getDistinctCount());
    }

    public function testCacheClearing()
    {
        $cache = new RuntimeArrayCacheMock();

        static::assertTrue($cache->set('test', null));
        static::assertTrue($cache->set('test1', null));
        static::assertTrue($cache->set('test2', null));

        static::assertEquals(['test' => null, 'test1' => null, 'test2' => null], $cache->getAll());

        static::assertEquals(['test' => null, 'test1' => null, 'test2' => null], $cache->flush());

        static::assertEquals([], $cache->getAll());

        static::assertTrue($cache->set('test', null));
        static::assertTrue($cache->set('test1', null));
        static::assertTrue($cache->set('test2', null));

        static::assertEquals(['test' => null, 'test1' => null, 'test2' => null], $cache->getAll());

        static::assertEquals(['test' => null, 'test1' => null, 'test2' => null], $cache->flush());

        static::assertEquals([], $cache->getAll());

        static::assertTrue($cache->set('test', null));
        static::assertTrue($cache->set('test1', null));
        static::assertTrue($cache->set('test1_1', null));
        static::assertTrue($cache->set('test2', null));
        static::assertTrue($cache->set('test11', null));

        static::assertEquals(['test' => null, 'test1' => null, 'test1_1' => null, 'test2' => null, 'test11' => null], $cache->getAll());

        static::assertEquals(['test1' => null, 'test1_1' => null], $cache->flush('test1'));

        static::assertEquals(['test' => null, 'test2' => null, 'test11' => null], $cache->getAll());
    }
}
