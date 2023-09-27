<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2019-2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\file;

use humhub\modules\file\libs\FileModuleMetadata;
use humhub\modules\file\libs\FileVariantMetadata;
use humhub\modules\file\libs\Metadata;
use humhub\modules\file\Module;
use tests\codeception\_support\HumHubDbTestCase;
use humhub\components\Event;
use yii\base\InvalidConfigException;

class MetadataTest extends HumHubDbTestCase
{
    private static $useData = false;

    public function _fixtures(): array
    {
        return self::$useData
            ? parent::_fixtures()
            : [];
    }

    public function testInstantiationMetadataWithoutModules()
    {
        // Disable Module Registration for Metadata
        Event::on(Metadata::class, Metadata::EVENT_INIT, [self::class, 'onMetadataInit'], null, false);

        $serialized = 'O:33:"humhub\modules\file\libs\Metadata":2:{s:1:"v";i:1;s:6:"config";O:8:"stdClass":1:{s:1:"v";i:1;}}';

        $instance = new Metadata();

        static::assertInstanceOf(Metadata::class, $instance);
        static::assertEquals([], $instance->fields());
        static::assertEquals($serialized, $instance->serialize());

        $instance = new Metadata(['foo' => 'bar']);
        static::assertInstanceOf(Metadata::class, $instance);
        static::assertCount(0, $instance);
        static::assertEquals([], $instance->fields());
        static::assertEquals([], $instance->toArray());
        static::assertEquals($serialized, $instance->serialize());

        $instance = new Metadata('O:33:"humhub\modules\file\libs\Metadata":3:{s:1:"v";i:1;i:0;a:1:{s:3:"foo";s:3:"bar";}s:6:"config";O:8:"stdClass":5:{s:1:"v";i:1;s:1:"0";a:1:{s:7:"default";N;}s:1:"1";b:0;s:1:"2";b:0;s:1:"3";b:0;}}');
        static::assertInstanceOf(Metadata::class, $instance);
        static::assertCount(0, $instance);
        static::assertEquals([], $instance->fields());
        static::assertEquals([], $instance->toArray());
        static::assertEquals($serialized, $instance->serialize());

        Event::off(Metadata::class, Metadata::EVENT_INIT, [self::class, 'onMetadataInit']);

        // Disable Module Registration
        Event::on(Metadata::class, Metadata::EVENT_REGISTER, [self::class, 'onMetadataRegister'], [], false);

        $instance = new Metadata();

        static::assertInstanceOf(Metadata::class, $instance);
        static::assertEquals([], $instance->fields());
        static::assertEquals([], $instance->toArray());
        static::assertEquals($serialized, $instance->serialize());

        Event::off(Metadata::class, Metadata::EVENT_REGISTER, [self::class, 'onMetadataRegister']);

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(sprintf("Module '%s' has not registered it's metadata namespace 'file'.", Module::class));

        static::assertInstanceOf(FileModuleMetadata::class, $instance->file);
    }

    public function testInstantiationMetadataWithModuleFile()
    {
        // Disable Module Registration for Metadata, except for file
        Event::on(Metadata::class, Metadata::EVENT_REGISTER, [self::class, 'onMetadataRegister'], ['file'], false);

        $serialized = 'O:33:"humhub\modules\file\libs\Metadata":3:{s:1:"v";i:1;s:2:"_0";a:1:{s:4:"file";O:43:"humhub\modules\file\libs\FileModuleMetadata":1:{s:1:"v";i:1;}}s:6:"config";O:8:"stdClass":1:{s:1:"v";i:1;}}';

        $instance = new Metadata();

        static::assertInstanceOf(Metadata::class, $instance);
        static::assertEquals(['file' => 'file'], $instance->fields());
        static::assertEquals(['file' => []], $instance->toArray());
        static::assertEquals($serialized, $instance->serialize());

        static::assertInstanceOf(FileModuleMetadata::class, $instance->file);
        static::assertInstanceOf(FileVariantMetadata::class, $instance->file->_draft);

        $serialized = 'O:33:"humhub\modules\file\libs\Metadata":3:{s:1:"v";i:1;s:2:"_0";a:1:{s:4:"file";O:43:"humhub\modules\file\libs\FileModuleMetadata":2:{s:1:"v";i:1;s:2:"_0";a:1:{s:3:"foo";s:3:"bar";}}}s:6:"config";O:8:"stdClass":1:{s:1:"v";i:1;}}';

        $instance->file->foo = 'bar';
        static::assertCount(1, $instance);
        static::assertEquals(['file' => 'file'], $instance->fields());
        static::assertEquals(['file' => ['foo' => 'bar']], $instance->toArray());
        static::assertEquals($serialized, $instance->serialize());

        $instance = new Metadata($serialized);
        static::assertInstanceOf(Metadata::class, $instance);
        static::assertCount(1, $instance);
        static::assertEquals(['file' => 'file'], $instance->fields());
        static::assertEquals(['file' => ['foo' => 'bar']], $instance->toArray());
        static::assertEquals($serialized, $instance->serialize());
    }

    public static function onMetadataInit(Event $e)
    {
        if ($e->name !== Metadata::EVENT_INIT) {
            return;
        }

        $metadata = $e->sender;

        if (!$metadata instanceof Metadata) {
            return;
        }

        $e->handled = true;
    }

    public static function onMetadataRegister(Event $e)
    {
        if ($e->name !== Metadata::EVENT_REGISTER) {
            return;
        }

        $metadata = $e->sender;

        if (!$metadata instanceof Metadata) {
            return;
        }

        if ($e->data === null) {
            return;
        }

        $e->data = (array)$e->data;

        if (!in_array($e->result['name'] ?? null, $e->data, true)) {
            $e->result = null;
            $e->handled = true;
        }
    }
}
