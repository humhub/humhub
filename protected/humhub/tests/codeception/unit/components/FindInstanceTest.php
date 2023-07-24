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

use Exception;
use humhub\exceptions\InvalidArgumentTypeException;
use humhub\exceptions\InvalidConfigTypeException;
use humhub\interfaces\FindInstanceInterface;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\BaseObject;
use yii\web\HttpException;

class FindInstanceTest extends HumHubDbTestCase
{
    public function testCreateTestInstance()
    {
        static::assertInstanceOf(FindInstanceInterface::class, $instance = new FindInstanceMock(1, [2]));
        static::assertEquals(1, $instance->args[0], 'First argument does not match!');
        static::assertEquals([2], $instance->args[1], 'Second argument does not match!');
    }

    public function testSelfIdentifier()
    {
        $instance = new FindInstanceMock();

        static::assertInstanceOf(FindInstanceInterface::class, $instance2 = FindInstanceMock::findInstance($instance));
        static::assertEquals($instance, $instance2);
    }

    public function testOnEmptyConfig()
    {
        $instance = new FindInstanceMock();

        static::assertEquals(null, FindInstanceMock::findInstance(null, ['onEmpty' => null]));
        static::assertEquals($instance, FindInstanceMock::findInstance(null, ['onEmpty' => $instance]));

        static::assertEquals(null, FindInstanceMock::findInstance('', ['onEmpty' => null]));
        static::assertEquals($instance, FindInstanceMock::findInstance('', ['onEmpty' => $instance]));

        static::assertEquals(null, FindInstanceMock::findInstance([], ['onEmpty' => null]));
        static::assertEquals($instance, FindInstanceMock::findInstance([], ['onEmpty' => $instance]));

        static::assertInstanceOf(FindInstanceInterface::class, FindInstanceMock::findInstance(0, ['onEmpty' => null]));
        static::assertInstanceOf(FindInstanceInterface::class, FindInstanceMock::findInstance('0', ['onEmpty' => null]));

        static::assertInstanceOf(FindInstanceInterface::class, FindInstanceMock::findInstance(108, ['onEmpty' => null]));
        static::assertInstanceOf(FindInstanceInterface::class, FindInstanceMock::findInstance('108', ['onEmpty' => null]));
    }

    public function testIntKeyConfig()
    {
        static::assertInstanceOf(FindInstanceInterface::class, $instance = FindInstanceMock::findInstance(1, ['intKey' => null]));
        static::assertEquals(['id' => 1], $instance->args[0]);

        static::assertInstanceOf(FindInstanceInterface::class, $instance = FindInstanceMock::findInstance(1, ['intKey' => 'guid']));
        static::assertEquals(['guid' => 1], $instance->args[0]);

        $this->expectException(InvalidArgumentTypeException::class);
        $this->expectExceptionMessage('Argument #1 $identifier passed to humhub\components\FindInstanceTrait::findInstanceHelper must be of type humhub\tests\codeception\unit\components\FindInstanceMock, int, (int)string - string given.');

        FindInstanceMock::findInstance('test', ['intKey' => 'guid']);
    }

    public function testStringKeyConfig()
    {
        static::assertInstanceOf(FindInstanceInterface::class, $instance = FindInstanceMock::findInstance(1, ['stringKey' => null]));
        static::assertEquals(['id' => 1], $instance->args[0]);

        static::assertInstanceOf(FindInstanceInterface::class, $instance = FindInstanceMock::findInstance(1, ['stringKey' => 'guid']));
        static::assertEquals(['id' => 1], $instance->args[0]);

        static::assertInstanceOf(FindInstanceInterface::class, $instance = FindInstanceMock::findInstance('test', ['stringKey' => 'guid']));
        static::assertEquals(['guid' => 'test'], $instance->args[0]);
    }

    public function testExceptionOnNullIdentifier()
    {
        $this->expectException(InvalidArgumentTypeException::class);
        $this->expectExceptionMessage('Argument #1 $identifier passed to humhub\components\FindInstanceTrait::findInstanceHelper must be of type humhub\tests\codeception\unit\components\FindInstanceMock, int, (int)string - null given.');

        FindInstanceMock::findInstance(null);
    }

    public function testExceptionOnEmptyStringIdentifier()
    {
        $this->expectException(InvalidArgumentTypeException::class);
        $this->expectExceptionMessage('Argument #1 $identifier passed to humhub\components\FindInstanceTrait::findInstanceHelper must be of type humhub\tests\codeception\unit\components\FindInstanceMock, int, (int)string - string given.');

        FindInstanceMock::findInstance('');
    }

    public function testExceptionOnEmptyArrayIdentifier()
    {
        $this->expectException(InvalidArgumentTypeException::class);
        $this->expectExceptionMessage('Argument #1 $identifier passed to humhub\components\FindInstanceTrait::findInstanceHelper must be of type humhub\tests\codeception\unit\components\FindInstanceMock, int, (int)string - array given.');

        FindInstanceMock::findInstance([]);
    }

    public function testCustomExceptionInstance()
    {
        $e = new HttpException(404);
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('');

        FindInstanceMock::findInstance(null, ['exception' => $e]);
    }

    public function testCustomExceptionClass()
    {
        $e = Exception::class;
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('');

        FindInstanceMock::findInstance(null, ['exception' => $e]);
    }

    public function testInvalidCustomExceptionClass()
    {
        $e = BaseObject::class;
        $this->expectException(InvalidConfigTypeException::class);
        $this->expectExceptionMessage('Parameter \'exception\' of configuration passed to humhub\components\FindInstanceTrait::findInstanceHelper must be of type Throwable, null - string given.');

        FindInstanceMock::findInstance(null, ['exception' => $e]);
    }

    public function testInvalidCustomExceptionConfigType()
    {
        $e = [];
        $this->expectException(InvalidConfigTypeException::class);
        $this->expectExceptionMessage('Parameter \'exception\' of configuration passed to humhub\components\FindInstanceTrait::findInstanceHelper must be of type Throwable, null - array given.');

        FindInstanceMock::findInstance(null, ['exception' => $e]);
    }
}
