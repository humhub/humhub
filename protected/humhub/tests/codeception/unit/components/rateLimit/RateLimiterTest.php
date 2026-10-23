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
use humhub\components\rateLimit\StorageInterface;
use Yii;
use yii\caching\ArrayCache;
use yii\helpers\ArrayHelper;

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
