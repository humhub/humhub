<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components\rateLimit;

use Codeception\Test\Unit;
use humhub\components\rateLimit\CacheStorage;
use humhub\components\rateLimit\RateLimiter;
use humhub\components\rateLimit\RedisStorage;
use humhub\components\rateLimit\StorageInterface;
use Yii;
use yii\caching\ArrayCache;
use yii\helpers\ArrayHelper;
use yii\log\Logger;
use yii\redis\Cache as RedisCache;
use yii\redis\ConnectionInterface;

/**
 * @since 1.20
 */
class RateLimiterTest extends Unit
{
    private function limiter(array $policies, ?StorageInterface $storage = null, int $now = 1000): TestRateLimiter
    {
        $limiter = new TestRateLimiter([
            'policies' => $policies,
            'storage' => $storage ?? new MemoryStorage(),
        ]);
        $limiter->time = $now;
        return $limiter;
    }

    public function testBurstWindowRefusesAfterTheLimit()
    {
        $limiter = $this->limiter(['p' => ['burst' => ['limit' => 2, 'span' => 60]]]);

        $this->assertTrue($limiter->hit('p', 'u1')->allowed);
        $this->assertTrue($limiter->hit('p', 'u1')->allowed);
        $result = $limiter->hit('p', 'u1');

        $this->assertFalse($result->allowed);
        $this->assertSame('burst', $result->window);
        $this->assertSame(0, $result->remaining);
        $this->assertSame(20, $result->retryAfter); // 1000 % 60 = 40 -> resets at 1020
        $this->assertSame(20, $result->resetsIn);
        $this->assertTrue($limiter->hit('p', 'u2')->allowed, 'Other subjects have their own budget');
    }

    public function testLongWindowRefusesWhileBurstStillAllows()
    {
        $limiter = $this->limiter(['p' => ['burst' => ['limit' => 100, 'span' => 60], 'long' => ['limit' => 2, 'span' => 3600]]]);

        $limiter->hit('p', 'u1');
        $limiter->hit('p', 'u1');
        $result = $limiter->hit('p', 'u1');

        $this->assertFalse($result->allowed);
        $this->assertSame('long', $result->window);
        $this->assertSame(3600 - 1000 % 3600, $result->retryAfter);
    }

    public function testDecidingWindowIsTheOneWithTheFewestRemaining()
    {
        $limiter = $this->limiter(['p' => ['burst' => ['limit' => 10, 'span' => 60], 'long' => ['limit' => 5, 'span' => 3600]]]);

        $result = $limiter->hit('p', 'u1');

        $this->assertTrue($result->allowed);
        $this->assertSame('long', $result->window);
        $this->assertSame(5, $result->limit);
        $this->assertSame(4, $result->remaining);
        $this->assertNull($result->retryAfter);
    }

    public function testTieGoesToTheShorterWindow()
    {
        $limiter = $this->limiter(['p' => ['long' => ['limit' => 5, 'span' => 3600], 'burst' => ['limit' => 5, 'span' => 60]]]);

        $this->assertSame('burst', $limiter->hit('p', 'u1')->window);
    }

    public function testUnknownDisabledPolicyAndDisabledLimiterAreUnlimitedAndNotCounted()
    {
        $storage = new MemoryStorage();
        $limiter = $this->limiter(['off' => false, 'on' => ['w' => ['limit' => 1, 'span' => 60]]], $storage);

        $this->assertTrue($limiter->hit('unknown', 'u1')->allowed);
        $this->assertTrue($limiter->hit('off', 'u1')->allowed);
        $this->assertNull($limiter->getPolicy('off'));
        $this->assertNull($limiter->getPolicy('unknown'));
        $this->assertSame([], $storage->counters);

        $limiter->enabled = false;
        $this->assertTrue($limiter->hit('on', 'u1')->allowed);
        $this->assertTrue($limiter->hit('on', 'u1')->allowed);
        $this->assertSame([], $storage->counters);
        $this->assertSame([], $limiter->hit('on', 'u1')->toHeaders());
    }

    public function testStorageFailureAllowsTheRequest()
    {
        $storage = new MemoryStorage();
        $storage->fail = true;
        $limiter = $this->limiter(['p' => ['burst' => ['limit' => 1, 'span' => 60]]], $storage);

        $result = $limiter->hit('p', 'u1');

        $this->assertTrue($result->allowed);
        $this->assertSame([], $result->toHeaders());
    }

    public function testHeaders()
    {
        $limiter = $this->limiter(['p' => ['burst' => ['limit' => 2, 'span' => 60], 'long' => ['limit' => 50, 'span' => 3600]]]);

        $headers = $limiter->hit('p', 'u1')->toHeaders();
        $this->assertSame('"burst";q=2;w=60, "long";q=50;w=3600', $headers['RateLimit-Policy']);
        $this->assertSame('"burst";r=1;t=20', $headers['RateLimit']);
        $this->assertArrayNotHasKey('Retry-After', $headers);

        $limiter->hit('p', 'u1');
        $headers = $limiter->hit('p', 'u1')->toHeaders();
        $this->assertSame('"burst";r=0;t=20', $headers['RateLimit']);
        $this->assertSame('20', (string)$headers['Retry-After']);
    }

    public function testNewWindowStartResetsTheCount()
    {
        $limiter = $this->limiter(['p' => ['burst' => ['limit' => 1, 'span' => 60]]], null, 1000);

        $this->assertTrue($limiter->hit('p', 'u1')->allowed);
        $this->assertFalse($limiter->hit('p', 'u1')->allowed);

        $limiter->time = 1020; // next window (1020 = 17 * 60)
        $this->assertTrue($limiter->hit('p', 'u1')->allowed);
    }

    public function testPolicyOverridesMergeByWindowNameAndKey()
    {
        $defaults = ['p' => ['burst' => ['limit' => 300, 'span' => 60], 'long' => ['limit' => 5000, 'span' => 3600]], 'q' => ['w' => ['limit' => 1, 'span' => 60]]];

        $limitOnly = $this->limiter(ArrayHelper::merge($defaults, ['p' => ['burst' => ['limit' => 30]]]));
        $this->assertSame(
            ['burst' => ['limit' => 30, 'span' => 60], 'long' => ['limit' => 5000, 'span' => 3600]],
            $limitOnly->getPolicy('p'),
        );

        $full = $this->limiter(ArrayHelper::merge($defaults, ['p' => ['burst' => ['limit' => 10, 'span' => 30]]]));
        $this->assertSame(['limit' => 10, 'span' => 30], $full->getPolicy('p')['burst']);

        $off = $this->limiter(ArrayHelper::merge($defaults, ['p' => false]));
        $this->assertNull($off->getPolicy('p'));
        $this->assertNotNull($off->getPolicy('q'));
    }

    public function testInvalidWindowsAreSkipped()
    {
        $limiter = $this->limiter(['p' => [
            'ok' => ['limit' => 5, 'span' => 60],
            'zero' => ['limit' => 0, 'span' => 60],
            'missing' => ['limit' => 5],
            'string' => ['limit' => '5', 'span' => 60],
        ], 'bad' => ['x' => ['limit' => 1]]]);

        $this->assertSame(['ok' => ['limit' => 5, 'span' => 60]], $limiter->getPolicy('p'));
        $this->assertNull($limiter->getPolicy('bad'));
    }

    public function testRedisStorageIncrementsAtomically()
    {
        $redis = $this->createMock(ConnectionInterface::class);
        $calls = [];
        $redis->method('executeCommand')->willReturnCallback(function (string $name, array $params) use (&$calls) {
            $calls[] = [$name, $params];
            return 3;
        });

        $this->assertSame(3, (new RedisStorage($redis))->increment('k', 60));
        $this->assertCount(1, $calls);
        $this->assertSame('EVAL', $calls[0][0]);
        $this->assertStringContainsString("redis.call('INCR', KEYS[1])", $calls[0][1][0]);
        $this->assertSame([1, 'k', 60], array_slice($calls[0][1], 1));
    }

    public function testStorageAutodetection()
    {
        $cacheBackup = Yii::$app->cache;
        try {
            Yii::$app->set('cache', new ArrayCache());
            $this->assertInstanceOf(CacheStorage::class, (new RateLimiter())->getStorage());

            $redis = $this->createMock(ConnectionInterface::class);
            Yii::$app->set('redis', $redis);
            $this->assertInstanceOf(RedisStorage::class, (new RateLimiter())->getStorage());
            Yii::$app->clear('redis');

            Yii::$app->set('cache', new RedisCache(['redis' => $redis]));
            $this->assertInstanceOf(RedisStorage::class, (new RateLimiter())->getStorage());
        } finally {
            Yii::$app->set('cache', $cacheBackup);
            Yii::$app->clear('redis');
        }
    }

    public function testKeysCarryTheInstancePrefix()
    {
        $cacheBackup = Yii::$app->cache;
        Yii::$app->set('cache', new ArrayCache(['keyPrefix' => 'inst1']));
        try {
            $limiter = new TestRateLimiter(['policies' => ['p' => ['w' => ['limit' => 5, 'span' => 60]]]]);
            $limiter->hit('p', 'u1');
            $this->assertSame(1, (int)Yii::$app->cache->get('rateLimit:inst1:p:w:u1:960'));
        } finally {
            Yii::$app->set('cache', $cacheBackup);
        }
    }

    public function testRefusalIsLoggedOnlyOnce()
    {
        $original = Yii::getLogger();
        $spy = new class () extends Logger {
            public array $seen = [];

            public function log($message, $level, $category = 'application')
            {
                $this->seen[] = [$level, $category, (string)$message];
            }
        };
        Yii::setLogger($spy);
        try {
            $limiter = $this->limiter(['once-policy' => ['w' => ['limit' => 1, 'span' => 60]]]);
            for ($i = 0; $i < 4; $i++) {
                $limiter->hit('once-policy', 'u1');
            }
        } finally {
            Yii::setLogger($original);
        }

        $this->assertCount(1, $spy->seen);
        $this->assertSame([Logger::LEVEL_WARNING, 'rateLimit'], array_slice($spy->seen[0], 0, 2));
    }

    public function testAllWindowsAreCountedAfterOneIsExhausted()
    {
        $storage = new MemoryStorage();
        $limiter = $this->limiter(['p' => ['burst' => ['limit' => 1, 'span' => 60], 'long' => ['limit' => 10, 'span' => 3600]]], $storage);

        $limiter->hit('p', 'u1');
        $limiter->hit('p', 'u1');
        $result = $limiter->hit('p', 'u1');

        $this->assertSame('burst', $result->window);
        $this->assertSame([3, 3], array_values($storage->counters));
    }

    public function testLastSecondOfAWindowResetsInOne()
    {
        $limiter = $this->limiter(['p' => ['w' => ['limit' => 1, 'span' => 60]]], null, 1019);

        $this->assertSame(1, $limiter->hit('p', 'u1')->resetsIn);
    }

    public function testCacheStorageCounts()
    {
        $cacheBackup = Yii::$app->cache;
        Yii::$app->set('cache', new ArrayCache());
        try {
            $storage = new CacheStorage();
            $this->assertSame(1, $storage->increment('k', 60));
            $this->assertSame(2, $storage->increment('k', 60));
            $this->assertSame(1, $storage->increment('other', 60));
        } finally {
            Yii::$app->set('cache', $cacheBackup);
        }
    }
}

class TestRateLimiter extends RateLimiter
{
    public int $time = 1000;

    protected function now(): int
    {
        return $this->time;
    }
}

class MemoryStorage implements StorageInterface
{
    public array $counters = [];
    public bool $fail = false;

    public function increment(string $key, int $ttl): int
    {
        if ($this->fail) {
            throw new \RuntimeException('down');
        }
        return $this->counters[$key] = ($this->counters[$key] ?? 0) + 1;
    }
}
