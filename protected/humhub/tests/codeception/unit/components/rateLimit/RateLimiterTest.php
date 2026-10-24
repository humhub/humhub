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
use yii\base\Exception;
use yii\caching\ArrayCache;
use yii\helpers\ArrayHelper;
use yii\log\Logger;
use yii\redis\Cache as RedisCache;
use yii\redis\Connection as RedisConnection;
use yii\redis\ConnectionInterface;

/**
 * @since 1.20
 */
class RateLimiterTest extends Unit
{
    private $cacheBackup;

    protected function _before()
    {
        // A pause after a storage failure is kept in the cache; never in the shared one
        $this->cacheBackup = Yii::$app->cache;
        Yii::$app->set('cache', new ArrayCache());
    }

    protected function _after()
    {
        Yii::$app->set('cache', $this->cacheBackup);
        Yii::$app->clear('redis');
    }

    private function logSpy(): Logger
    {
        $spy = new class extends Logger {
            public array $seen = [];

            public function log($message, $level, $category = 'application')
            {
                if ($category === 'rateLimit') {
                    $this->seen[] = [$level, $category, (string)$message];
                }
            }
        };
        Yii::setLogger($spy);
        return $spy;
    }

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

    public function testStorageFailurePausesRateLimitingAcrossRequests()
    {
        $policies = ['p' => ['burst' => ['limit' => 1, 'span' => 60]]];
        $storage = new MemoryStorage();
        $storage->fail = true;

        $original = Yii::getLogger();
        $spy = $this->logSpy();
        try {
            $limiter = $this->limiter($policies, $storage);
            $limiter->hit('p', 'u1');
            $limiter->hit('p', 'u1');

            // Another request within the pause: the cache (not the failing store) keeps it
            $next = $this->limiter($policies, $storage, 1059);
            $this->assertTrue($next->hit('p', 'u1')->allowed);
            $this->assertSame(1, $storage->calls, 'No storage call during the pause');
            $this->assertCount(1, $spy->seen, 'The failure is logged once per pause');
            $this->assertSame(Logger::LEVEL_ERROR, $spy->seen[0][0]);

            // After the pause the store is tried again
            $later = $this->limiter($policies, $storage, 1060);
            $later->hit('p', 'u1');
            $this->assertSame(2, $storage->calls);
            $this->assertCount(2, $spy->seen);

            $storage->fail = false;
            $recovered = $this->limiter($policies, $storage, 1120);
            $this->assertSame('burst', $recovered->hit('p', 'u1')->window);
        } finally {
            Yii::setLogger($original);
        }
    }

    public function testStorageFailurePausesOnlyThisRequestWhenTheCacheIsTheStore()
    {
        Yii::$app->set('cache', new RedisCache(['redis' => $this->createMock(ConnectionInterface::class)]));
        $policies = ['p' => ['burst' => ['limit' => 1, 'span' => 60]]];
        $storage = new MemoryStorage();
        $storage->fail = true;

        $original = Yii::getLogger();
        $spy = $this->logSpy();
        try {
            $limiter = $this->limiter($policies, $storage);
            $limiter->hit('p', 'u1');
            $this->assertTrue($limiter->hit('p', 'u1')->allowed);
            $this->assertSame(1, $storage->calls, 'Paused for the rest of the request');

            $this->limiter($policies, $storage)->hit('p', 'u1');
            $this->assertSame(2, $storage->calls, 'The next request tries again');
            $this->assertCount(2, $spy->seen);
        } finally {
            Yii::setLogger($original);
        }
    }

    public function testCacheStorageFailurePausesOnlyThisRequest()
    {
        $cache = $this->createMock(ArrayCache::class);
        $cache->expects($this->once())->method('add')->willThrowException(new Exception('cache down'));
        Yii::$app->set('cache', $cache);

        $limiter = $this->limiter(['p' => ['burst' => ['limit' => 1, 'span' => 60]]], new CacheStorage());
        $this->assertTrue($limiter->hit('p', 'u1')->allowed);
        $this->assertTrue($limiter->hit('p', 'u1')->allowed);
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

    public function testRedisStorageCountsAllWindowsInOneScript()
    {
        $redis = $this->createMock(ConnectionInterface::class);
        $calls = [];
        $redis->method('executeCommand')->willReturnCallback(function (string $name, array $params) use (&$calls) {
            $calls[] = [$name, $params];
            return ['3', '4'];
        });

        $values = (new RedisStorage($redis))->increment(['k1' => ['ttl' => 60, 'limit' => 5], 'k2' => ['ttl' => 3600, 'limit' => 50]]);

        $this->assertSame([3, 4], $values);
        $this->assertCount(1, $calls);
        $this->assertSame('EVAL', $calls[0][0]);
        $this->assertStringContainsString("redis.call('INCR', key)", $calls[0][1][0]);
        $this->assertSame([2, 'k1', 'k2', 60, 5, 3600, 50], array_slice($calls[0][1], 1));
    }

    public function testRedisScriptStopsAfterTheFirstExceededCounter()
    {
        $redis = new RedisConnection(['hostname' => '127.0.0.1', 'database' => 15, 'connectionTimeout' => 1]);
        try {
            $redis->open();
        } catch (\Throwable) {
            $this->markTestSkipped('No local Redis');
        }

        $keys = ['rateLimitTest:' . uniqid() . ':a', 'rateLimitTest:' . uniqid() . ':b'];
        $counters = [$keys[0] => ['ttl' => 60, 'limit' => 1], $keys[1] => ['ttl' => 3600, 'limit' => 10]];
        try {
            $storage = new RedisStorage($redis);
            $this->assertSame([1, 1], $storage->increment($counters));
            $this->assertSame([2], $storage->increment($counters));
            $this->assertSame([3], $storage->increment($counters));
            $this->assertSame('1', (string)$redis->executeCommand('GET', [$keys[1]]));
            $ttl = (int)$redis->executeCommand('TTL', [$keys[1]]);
            $this->assertTrue($ttl > 3500 && $ttl <= 3600);
        } finally {
            $redis->executeCommand('DEL', $keys);
            $redis->close();
        }
    }

    public function testStorageAutodetection()
    {
        $this->assertInstanceOf(CacheStorage::class, (new RateLimiter())->getStorage());

        $redis = $this->createMock(ConnectionInterface::class);
        Yii::$app->set('redis', $redis);
        $this->assertInstanceOf(RedisStorage::class, (new RateLimiter())->getStorage());
        Yii::$app->clear('redis');

        Yii::$app->set('cache', new RedisCache(['redis' => $redis]));
        $this->assertInstanceOf(RedisStorage::class, (new RateLimiter())->getStorage());
    }

    public function testKeysCarryTheInstancePrefix()
    {
        Yii::$app->set('cache', new ArrayCache(['keyPrefix' => 'inst1']));

        $limiter = new TestRateLimiter(['policies' => ['p' => ['w' => ['limit' => 5, 'span' => 60]]]]);
        $limiter->hit('p', 'u1');
        $this->assertSame('inst1', $limiter->getKeyPrefix());
        $this->assertSame(1, (int)Yii::$app->cache->get('rateLimit:inst1:p:w:u1:960'));

        Yii::$app->set('cache', new RedisCache(['redis' => $this->createMock(ConnectionInterface::class), 'keyPrefix' => 'inst2']));
        $this->assertSame('inst2', (new RateLimiter())->getKeyPrefix());
    }

    public function testKeyPrefixFallsBackToTheInstallationSecret()
    {
        $secret = Yii::$app->settings->get('secret');
        try {
            Yii::$app->settings->set('secret', 'secret-of-instance-1');
            $expected = substr(hash('sha256', 'secret-of-instance-1'), 0, 12);

            // A separate redis component: the cache prefix does not separate its keys
            Yii::$app->set('cache', new ArrayCache(['keyPrefix' => 'inst1']));
            Yii::$app->set('redis', $this->createMock(ConnectionInterface::class));
            $this->assertSame($expected, (new RateLimiter())->getKeyPrefix());
            Yii::$app->clear('redis');

            // A cache without prefix, or a configured store
            Yii::$app->set('cache', new ArrayCache(['keyPrefix' => '']));
            $this->assertSame($expected, (new RateLimiter())->getKeyPrefix());
            $this->assertSame($expected, (new RateLimiter(['storage' => new MemoryStorage()]))->getKeyPrefix());

            Yii::$app->settings->set('secret', 'secret-of-instance-2');
            $this->assertNotSame($expected, (new RateLimiter(['storage' => new MemoryStorage()]))->getKeyPrefix());

            Yii::$app->settings->set('secret', '');
            $this->assertSame(Yii::$app->id, (new RateLimiter(['storage' => new MemoryStorage()]))->getKeyPrefix());
        } finally {
            Yii::$app->settings->set('secret', $secret);
        }
    }

    public function testConfiguredKeyPrefixWins()
    {
        Yii::$app->set('cache', new ArrayCache(['keyPrefix' => 'inst1']));
        $storage = new MemoryStorage();
        $limiter = $this->limiter(['p' => ['w' => ['limit' => 5, 'span' => 60]]], $storage);
        $limiter->keyPrefix = 'mine';

        $limiter->hit('p', 'u1');
        $this->assertSame(['rateLimit:mine:p:w:u1:960'], array_keys($storage->counters));
    }

    public function testRefusalIsLoggedOnlyOnce()
    {
        $original = Yii::getLogger();
        $spy = $this->logSpy();
        try {
            $limiter = $this->limiter(['once-policy' => ['w' => ['limit' => 1, 'span' => 60]]]);
            for ($i = 0; $i < 4; $i++) {
                $limiter->hit('once-policy', 'u1', "route 'api/v2/test'");
            }
        } finally {
            Yii::setLogger($original);
        }

        $this->assertCount(1, $spy->seen);
        $this->assertSame([Logger::LEVEL_WARNING, 'rateLimit'], array_slice($spy->seen[0], 0, 2));
        $this->assertStringContainsString("subject 'u1', route 'api/v2/test'.", $spy->seen[0][2]);
    }

    public function testLongerWindowsAreNotChargedOnceOneIsExhausted()
    {
        $storage = new MemoryStorage();
        // Configured long first: windows are counted by span, shortest first
        $limiter = $this->limiter(['p' => ['long' => ['limit' => 10, 'span' => 3600], 'burst' => ['limit' => 1, 'span' => 60]]], $storage);

        $limiter->hit('p', 'u1');
        $limiter->hit('p', 'u1');
        $result = $limiter->hit('p', 'u1');

        $this->assertFalse($result->allowed);
        $this->assertSame('burst', $result->window);
        $this->assertSame(['burst' => 3, 'long' => 1], [
            'burst' => $storage->counters[$this->key($limiter, 'burst', 960)],
            'long' => $storage->counters[$this->key($limiter, 'long', 0)],
        ]);
        $this->assertSame(3, $storage->calls, 'One storage round trip per hit');
        $this->assertSame(['long', 'burst'], array_keys($result->windows), 'Headers keep the configured order');
    }

    public function testShorterWindowsAreChargedWhenALongerOneIsExhausted()
    {
        $storage = new MemoryStorage();
        $limiter = $this->limiter(['p' => ['burst' => ['limit' => 100, 'span' => 60], 'long' => ['limit' => 1, 'span' => 3600]]], $storage);

        $limiter->hit('p', 'u1');
        $result = $limiter->hit('p', 'u1');

        $this->assertSame('long', $result->window);
        $this->assertSame([2, 2], array_values($storage->counters));
    }

    private function key(RateLimiter $limiter, string $window, int $start): string
    {
        return "rateLimit:{$limiter->getKeyPrefix()}:p:$window:u1:$start";
    }

    public function testLastSecondOfAWindowResetsInOne()
    {
        $limiter = $this->limiter(['p' => ['w' => ['limit' => 1, 'span' => 60]]], null, 1019);

        $this->assertSame(1, $limiter->hit('p', 'u1')->resetsIn);
    }

    public function testCacheStorageCounts()
    {
        $storage = new CacheStorage();
        $counters = ['k' => ['ttl' => 60, 'limit' => 1], 'other' => ['ttl' => 3600, 'limit' => 10]];

        $this->assertSame([1, 1], $storage->increment($counters));
        $this->assertSame([2], $storage->increment($counters), 'Stops after the exceeded counter');
        $this->assertSame(1, (int)Yii::$app->cache->get('other'));
        $this->assertSame([1], $storage->increment(['third' => ['ttl' => 60, 'limit' => 5]]));
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
    public int $calls = 0;

    public function increment(array $counters): array
    {
        $this->calls++;
        if ($this->fail) {
            throw new \RuntimeException('down');
        }

        $values = [];
        foreach ($counters as $key => ['limit' => $limit]) {
            $values[] = $this->counters[$key] = ($this->counters[$key] ?? 0) + 1;
            if ($this->counters[$key] > $limit) {
                break;
            }
        }
        return $values;
    }
}
