<?php

namespace tests\codeception\unit\modules\live;

use humhub\modules\live\driver\MercurePushDriver;
use tests\codeception\_support\HumHubDbTestCase;
use yii\helpers\Url;

class MercurePushDriverTest extends HumHubDbTestCase
{
    private const JWT_KEYS = [
        'jwtKeyPublisher' => 'test-publisher-key-0123456789abcdef',
        'jwtKeySubscriber' => 'test-subscriber-key-0123456789abcdef',
    ];

    public function testInternalHubUrlFallsBackToHubUrl()
    {
        $driver = new MercurePushDriver(array_merge(self::JWT_KEYS, [
            'hubUrl' => 'https://social.example.com/.well-known/mercure',
        ]));

        // Browser (subscribe) uses the public hub URL ...
        static::assertEquals('https://social.example.com/.well-known/mercure', $driver->hubUrl);
        static::assertEquals('https://social.example.com/.well-known/mercure', $driver->getJsConfig()['options']['url']);
        // ... and, with no internal URL configured, the server publishes to the same URL (legacy behaviour).
        static::assertEquals('https://social.example.com/.well-known/mercure', $driver->internalHubUrl);
    }

    public function testInternalHubUrlSplitsPublishFromSubscribe()
    {
        $driver = new MercurePushDriver(array_merge(self::JWT_KEYS, [
            'hubUrl' => 'https://social.example.com/.well-known/mercure',
            'internalHubUrl' => 'http://localhost/.well-known/mercure',
        ]));

        // Server publishes over loopback ...
        static::assertEquals('http://localhost/.well-known/mercure', $driver->internalHubUrl);
        // ... while the browser keeps subscribing via the public address.
        static::assertEquals('https://social.example.com/.well-known/mercure', $driver->hubUrl);
        static::assertEquals('https://social.example.com/.well-known/mercure', $driver->getJsConfig()['options']['url']);
    }

    public function testHubUrlDefaultsToPublicSiteAddress()
    {
        $driver = new MercurePushDriver(self::JWT_KEYS);

        $expected = Url::to('/.well-known/mercure', true);
        static::assertEquals($expected, $driver->hubUrl);
        static::assertEquals($expected, $driver->internalHubUrl);
    }
}
