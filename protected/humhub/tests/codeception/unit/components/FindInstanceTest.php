<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

/**
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace humhub\tests\codeception\unit\components;

use humhub\exceptions\InvalidArgumentTypeException;
use humhub\interfaces\FindInstanceInterface;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class FindInstanceTest extends HumHubDbTestCase
{
    public function testCreateTestInstance()
    {
        static::assertInstanceOf(FindInstanceInterface::class, $instance = new FindInstanceMock(1, [2]));
        static::assertEquals(1, $instance->args[0], 'First argument does not match!');
        static::assertEquals([2], $instance->args[1], 'Second argument does not match!');
    }

    public function testIdentifierOfTypeNull()
    {
        $value = null;

        $type = FindInstanceMock::validateInstanceIdentifier($value);

        static::assertEquals(FindInstanceInterface::INSTANCE_IDENTIFIER_IS_NULL, $type);
        static::assertNull($value);
    }

    public function testIdentifierOfTypeEmptyString()
    {
        $value = '';

        $type = FindInstanceMock::validateInstanceIdentifier($value);

        static::assertEquals(FindInstanceInterface::INSTANCE_IDENTIFIER_IS_NULL, $type);
        static::assertNull($value);
    }

    public function testIdentifierOfTypeStringWithSpaces()
    {
        $value = '  ';

        $type = FindInstanceMock::validateInstanceIdentifier($value);

        static::assertEquals(FindInstanceInterface::INSTANCE_IDENTIFIER_IS_NULL, $type);
        static::assertNull($value);
    }

    public function testIdentifierOfTypeEmptyArray()
    {
        $value = [];

        $type = FindInstanceMock::validateInstanceIdentifier($value);

        static::assertEquals(FindInstanceInterface::INSTANCE_IDENTIFIER_IS_NULL, $type);
        static::assertNull($value);
    }

    public function testIdentifierOfTypeSelf()
    {
        $value = new FindInstanceMock();

        $type = FindInstanceMock::validateInstanceIdentifier($value);

        static::assertEquals(FindInstanceInterface::INSTANCE_IDENTIFIER_IS_SELF, $type);
        static::assertInstanceOf(FindInstanceInterface::class, $value);
    }

    public function testIdentifierOfTypeStringWithZero()
    {
        $value = '0';

        $type = FindInstanceMock::validateInstanceIdentifier($value);

        static::assertEquals(FindInstanceInterface::INSTANCE_IDENTIFIER_IS_INT, $type);
        static::assertEquals(0, $value);
    }

    public function testIdentifierOfTypeStringWithInteger()
    {
        $value = '1234';

        $type = FindInstanceMock::validateInstanceIdentifier($value);

        static::assertEquals(FindInstanceInterface::INSTANCE_IDENTIFIER_IS_INT, $type);
        static::assertEquals(1234, $value);
    }

    public function testIdentifierOfTypeString()
    {
        $value = 'Hello World';

        $type = FindInstanceMock::validateInstanceIdentifier($value);

        static::assertEquals(FindInstanceInterface::INSTANCE_IDENTIFIER_IS_STRING, $type);
        static::assertEquals('Hello World', $value);
    }

    public function testIdentifierOfTypeStringWithKey()
    {
        $value = 'Hello World';

        $type = FindInstanceMock::validateInstanceIdentifier($value, 'key');

        static::assertEquals(FindInstanceInterface::INSTANCE_IDENTIFIER_IS_ARRAY, $type);
        static::assertEquals(['key' => 'Hello World'], $value);
    }

    public function testIdentifierOfTypeArray()
    {
        $value = ['Hello', 'World'];

        $type = FindInstanceMock::validateInstanceIdentifier($value, 'key');

        static::assertEquals(FindInstanceInterface::INSTANCE_IDENTIFIER_IS_ARRAY, $type);
        static::assertEquals(['Hello', 'World'], $value);
    }

    public function testSelfIdentifier()
    {
        $instance = new FindInstanceMock();

        static::assertInstanceOf(FindInstanceInterface::class, $instance2 = FindInstanceMock::findInstance($instance));
        static::assertEquals($instance, $instance2);
    }

    public function testExceptionOnNullIdentifier()
    {
        $this->expectException(InvalidArgumentTypeException::class);
        $this->expectExceptionMessage('Argument #1 $identifier passed to humhub\components\FindInstanceTrait::findInstance must be of type humhub\tests\codeception\unit\components\FindInstanceMock, int, (int)string, string, array - null given.');

        FindInstanceMock::findInstance(null);
    }

    public function testExceptionOnEmptyStringIdentifier()
    {
        $this->expectException(InvalidArgumentTypeException::class);
        $this->expectExceptionMessage('Argument #1 $identifier passed to humhub\components\FindInstanceTrait::findInstance must be of type humhub\tests\codeception\unit\components\FindInstanceMock, int, (int)string, string, array - string given.');

        FindInstanceMock::findInstance('');
    }

    public function testExceptionOnEmptyArrayIdentifier()
    {
        $this->expectException(InvalidArgumentTypeException::class);
        $this->expectExceptionMessage('Argument #1 $identifier passed to humhub\components\FindInstanceTrait::findInstance must be of type humhub\tests\codeception\unit\components\FindInstanceMock, int, (int)string, string, array - array given.');

        FindInstanceMock::findInstance([]);
    }

    public function testCache()
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
//        static::assertFalse($cache->valueRetrieved);
        static::assertEquals($instance, Yii::$app->runtimeCache->get($instance->getUniqueId()));

        $cache->resetState();

        // get the same object from cache
        static::assertInstanceOf(FindInstanceInterface::class, $instance2 = FindInstanceMock::findInstance(1));
        static::assertNotNull($cache->cacheRead);
        static::assertNull($cache->cacheWritten);
//        static::assertFalse($cache->valueRetrieved);
        static::assertEquals($instance, $cache->get('humhub_tests_codeception_unit_components_FindInstanceMock__1'));
        static::assertEquals(spl_object_id($instance2), spl_object_id($cache->get('humhub_tests_codeception_unit_components_FindInstanceMock__1')));
        static::assertEquals(spl_object_id($instance), spl_object_id($cache->get('humhub_tests_codeception_unit_components_FindInstanceMock__1')));

        // restore original cache
        Yii::$app->set('runtimeCache', $currentCache);
    }
}
