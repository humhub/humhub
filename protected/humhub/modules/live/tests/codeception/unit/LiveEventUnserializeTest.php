<?php

namespace tests\codeception\unit\modules\live;

use humhub\modules\content\live\NewContent;
use humhub\modules\live\components\LiveEvent;
use tests\codeception\_support\HumHubDbTestCase;

/**
 * Tests the secure deserialization of live events, which restricts unserialize()
 * to genuine {@see LiveEvent} subclasses to prevent PHP object injection.
 */
class LiveEventUnserializeTest extends HumHubDbTestCase
{
    public function testValidLiveEventIsRestored()
    {
        $event = new NewContent();
        $event->contentContainerId = 5;
        $event->contentId = 42;
        $event->originator = 'user-guid';

        $restored = LiveEvent::fromSerialized(serialize($event));

        static::assertInstanceOf(NewContent::class, $restored);
        static::assertSame(5, $restored->contentContainerId);
        static::assertSame(42, $restored->contentId);
        static::assertSame('user-guid', $restored->originator);
    }

    public function testNonLiveEventClassIsRejected()
    {
        // A top-level object that is not a LiveEvent subclass must never be instantiated.
        static::assertNull(LiveEvent::fromSerialized(serialize(new \stdClass())));
        static::assertNull(LiveEvent::fromSerialized(serialize(new \ArrayObject(['x' => 1]))));
    }

    public function testNestedForeignObjectIsNeutralized()
    {
        // Craft a valid top-level LiveEvent that smuggles a foreign object in one
        // of its properties — the gadget shape of an object injection payload.
        $event = new NewContent();
        $event->contentContainerId = 1;
        $event->sourceClass = new \ArrayObject(['payload' => true]);

        $restored = LiveEvent::fromSerialized(serialize($event));

        // The LiveEvent itself is restored...
        static::assertInstanceOf(NewContent::class, $restored);
        // ...but the nested foreign class is blocked and left incomplete, not live.
        static::assertInstanceOf(\__PHP_Incomplete_Class::class, $restored->sourceClass);
        static::assertNotInstanceOf(\ArrayObject::class, $restored->sourceClass);
    }

    public function testMalformedOrEmptyInputReturnsNull()
    {
        static::assertNull(LiveEvent::fromSerialized(null));
        static::assertNull(LiveEvent::fromSerialized(''));
        static::assertNull(LiveEvent::fromSerialized('not-serialized-data'));
        static::assertNull(LiveEvent::fromSerialized('b:1;'));           // serialized bool
        static::assertNull(LiveEvent::fromSerialized('a:0:{}'));          // serialized array
        static::assertNull(LiveEvent::fromSerialized('O:8:"NoSuch__":0:{}')); // unknown class
    }
}
