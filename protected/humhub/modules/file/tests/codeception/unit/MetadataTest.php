<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2019-2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\file;

use humhub\modules\file\libs\Metadata;
use tests\codeception\_support\HumHubDbTestCase;

class MetadataTest extends HumHubDbTestCase
{
    private static $useData = false;

    public function _fixtures(): array
    {
        return self::$useData
            ? parent::_fixtures()
            : [];
    }

    public function testInstantiationStdClass()
    {
        $serialized = 'O:33:"humhub\modules\file\libs\Metadata":2:{s:1:"v";i:1;s:6:"config";O:8:"stdClass":1:{s:1:"v";i:1;}}';

        $instance = new Metadata();

        static::assertInstanceOf(Metadata::class, $instance);
        static::assertCount(0, $instance);
        static::assertEquals([], $instance->fields());
        static::assertEquals($serialized, $instance->serialize());

        $serialized = 'O:33:"humhub\modules\file\libs\Metadata":3:{s:1:"v";i:1;s:2:"_0";a:1:{s:3:"foo";s:3:"bar";}s:6:"config";O:8:"stdClass":1:{s:1:"v";i:1;}}';

        $instance = new Metadata(['foo' => 'bar']);
        static::assertInstanceOf(Metadata::class, $instance);
        static::assertCount(1, $instance);
        static::assertEquals(['foo' => 'foo'], $instance->fields());
        static::assertEquals(['foo' => 'bar'], $instance->toArray());
        static::assertEquals($serialized, $instance->serialize());

        $instance = new Metadata($serialized);
        static::assertInstanceOf(Metadata::class, $instance);
        static::assertCount(1, $instance);
        static::assertEquals(['foo' => 'foo'], $instance->fields());
        static::assertEquals(['foo' => 'bar'], $instance->toArray());
        static::assertEquals($serialized, $instance->serialize());
    }
}
