<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2018-2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit;

use Codeception\Test\Unit;
use humhub\exceptions\InvalidArgumentTypeException;
use humhub\exceptions\InvalidArgumentValueException;
use humhub\libs\StdClass;
use humhub\libs\StdClassConfigurable;

/**
 * Class MimeHelperTest
 */
class StdClassTest extends Unit
{
    public function testInstantiationStdClass()
    {
        $serialized = 'O:20:"humhub\libs\StdClass":1:{s:1:"v";i:1;}';

        $instance = new StdClass();

        static::assertInstanceOf(StdClass::class, $instance);
        static::assertCount(0, $instance);
        static::assertEquals([], $instance->fields());
        static::assertEquals($serialized, $instance->serialize());
        static::assertEquals($serialized, serialize($instance));

        $instance = new StdClass([]);
        static::assertInstanceOf(StdClass::class, $instance);
        static::assertCount(0, $instance);
        static::assertEquals([], $instance->fields());
        static::assertEquals($serialized, $instance->serialize());

        $instance = new StdClass(null);
        static::assertInstanceOf(StdClass::class, $instance);
        static::assertCount(0, $instance);

        $instance = new StdClass($serialized);
        static::assertCount(0, $instance);
        static::assertEquals([], $instance->fields());
        static::assertEquals($serialized, $instance->serialize());

        $instance = new StdClass('O:20:"humhub\libs\StdClass":2:{s:1:"v";i:1;i:0;a:0:{}}');
        static::assertCount(0, $instance);
        static::assertEquals([], $instance->fields());
        static::assertEquals($serialized, $instance->serialize());

        $serialized = 'O:20:"humhub\libs\StdClass":2:{s:1:"v";i:1;s:2:"_0";a:1:{s:3:"foo";s:3:"bar";}}';

        $instance = new StdClass(['foo' => 'bar']);
        static::assertInstanceOf(StdClass::class, $instance);
        static::assertCount(1, $instance);
        static::assertEquals(['foo' => 'foo'], $instance->fields());
        static::assertEquals(['foo' => 'bar'], $instance->toArray());
        static::assertEquals($serialized, $instance->serialize());

        $instance = new StdClass($serialized);
        static::assertCount(1, $instance);
        static::assertEquals(['foo' => 'foo'], $instance->fields());
        static::assertEquals(['foo' => 'bar'], $instance->toArray());
        static::assertEquals($serialized, $instance->serialize());

        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessage('Argument $serialized passed to humhub\libs\StdClass::unserialize must be string starting with \'O:20:"humhub\libs\StdClass"\' - O:20:"some\dangerous\class":1:{s:4:"data";a:1:{s:3:"foo";s:3:"bar";}} given.');

        new StdClass('O:20:"some\\dangerous\\class":1:{s:4:"data";a:1:{s:3:"foo";s:3:"bar";}}');
    }

    public function testInvalidInstantiationStdClassWithTrue()
    {
        $this->expectException(InvalidArgumentTypeException::class);
        $this->expectExceptionMessage('Argument ...$args passed to humhub\libs\StdClass::addValues must be one of the following types: array, Traversable - bool given.');

        new StdClass(false);
    }

    public function testInvalidInstantiationStdClassWithFalse()
    {
        $this->expectException(InvalidArgumentTypeException::class);
        $this->expectExceptionMessage('Argument ...$args passed to humhub\libs\StdClass::addValues must be one of the following types: array, Traversable - bool given.');

        new StdClass(false);
    }

    public function testInstantiationStdClassConfigurable()
    {
        $serialized = 'O:32:"humhub\libs\StdClassConfigurable":2:{s:1:"v";i:1;s:6:"config";O:8:"stdClass":1:{s:1:"v";i:1;}}';

        $instance = new StdClassConfigurable();
        static::assertInstanceOf(StdClassConfigurable::class, $instance);
        static::assertCount(0, $instance);
        static::assertEquals([], $instance->fields());
        static::assertEquals($serialized, $instance->serialize());

        // try again with the now initialized config storage
        $instance = new StdClassConfigurable();
        static::assertInstanceOf(StdClassConfigurable::class, $instance);
        static::assertCount(0, $instance);

        // try with different parameter values

        $instance = new StdClassConfigurable([]);
        static::assertInstanceOf(StdClassConfigurable::class, $instance);
        static::assertCount(0, $instance);
        static::assertEquals([], $instance->fields());

        $instance = new StdClassConfigurable(null);
        static::assertInstanceOf(StdClassConfigurable::class, $instance);
        static::assertCount(0, $instance);
        static::assertEquals([], $instance->fields());

        $instance = new StdClassConfigurable($serialized);
        static::assertInstanceOf(StdClassConfigurable::class, $instance);
        static::assertCount(0, $instance);
        static::assertEquals([], $instance->fields());
        static::assertEquals($serialized, $instance->serialize());

        $instance = new StdClassConfigurable('O:32:"humhub\libs\StdClassConfigurable":3:{s:1:"v";i:1;i:0;a:0:{}s:6:"config";O:8:"stdClass":5:{s:1:"v";i:1;s:2:"_0";a:1:{s:7:"default";N;}s:2:"_1";b:0;s:2:"_2";b:0;s:2:"_3";b:0;}}');
        static::assertInstanceOf(StdClassConfigurable::class, $instance);
        static::assertCount(0, $instance);
        static::assertEquals([], $instance->fields());
        static::assertEquals($serialized, $instance->serialize());

        $serialized = 'O:32:"humhub\libs\StdClassConfigurable":3:{s:1:"v";i:1;s:2:"_0";a:1:{s:3:"foo";s:3:"bar";}s:6:"config";O:8:"stdClass":1:{s:1:"v";i:1;}}';

        $instance = new StdClassConfigurable(['foo' => 'bar']);
        static::assertInstanceOf(StdClassConfigurable::class, $instance);
        static::assertCount(1, $instance);
        static::assertEquals(['foo' => 'foo'], $instance->fields());
        static::assertEquals(['foo' => 'bar'], $instance->toArray());
        static::assertEquals($serialized, $instance->serialize());

        $instance = new StdClassConfigurable($serialized);
        static::assertInstanceOf(StdClassConfigurable::class, $instance);
        static::assertCount(1, $instance);
        static::assertEquals(['foo' => 'foo'], $instance->fields());
        static::assertEquals(['foo' => 'bar'], $instance->toArray());
        static::assertFalse($instance->isFixed());
        static::assertEquals($serialized, $instance->serialize());

        $serialized = 'O:32:"humhub\libs\StdClassConfigurable":3:{s:1:"v";i:1;s:2:"_0";a:1:{s:3:"foo";s:3:"bar";}s:6:"config";O:8:"stdClass":2:{s:1:"v";i:1;s:2:"_1";b:1;}}';

        $instance = StdClassConfigurable::create(['foo' => 'bar'])->fixate();
        static::assertInstanceOf(StdClassConfigurable::class, $instance);
        static::assertCount(1, $instance);
        static::assertEquals(['foo' => 'foo'], $instance->fields());
        static::assertEquals(['foo' => 'bar'], $instance->toArray());
        static::assertTrue($instance->isFixed(), 'StdClassConfigurable object is not fixated!');
        static::assertEquals($serialized, $instance->serialize());

        $instance = new StdClassConfigurable($serialized);
        static::assertInstanceOf(StdClassConfigurable::class, $instance);
        static::assertCount(1, $instance);
        static::assertEquals(['foo' => 'foo'], $instance->fields());
        static::assertTrue($instance->isFixed(), 'StdClassConfigurable object is not fixated!');
        static::assertEquals($serialized, $instance->serialize());
    }
}
