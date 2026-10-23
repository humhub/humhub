<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\rateLimit;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\caching\CacheInterface;
use yii\caching\DummyCache;
use yii\redis\Cache as RedisCache;

/**
 * Counts hits of named policies per subject in fixed time windows.
 *
 * A policy is a list of windows (`name => ['limit' => 300, 'span' => 60]`); a hit counts in the
 * windows, shortest span first, and is refused as soon as one of them is exhausted. The longer
 * windows are then not charged. A policy set to `false` is disabled.
 *
 * Config merging works by name: windows merge by name and their values by key, so an override
 * may change only the `limit`. A default window can only be removed by disabling the policy.
 *
 * See `docs/develop/concept-rate-limiting.md`.
 *
 * @since 1.20
 */
class RateLimiter extends Component
{
    private const PAUSE_KEY = 'rateLimit:pausedUntil';

    public bool $enabled = true;

    /**
     * @var array<string, array<string, array{limit: int, span: int}>|false> Policy name => windows, or false
     */
    public array $policies = [];

    /**
     * @var StorageInterface|array|null Instance or config; null selects the storage automatically
     */
    public $storage = null;

    /**
     * @var string|null Separates the counters of instances sharing one store. Null or empty: the
     * `keyPrefix` of the application cache when that cache (or its Redis) is the store, else a short
     * hash of the installation's `secret`, else the application ID.
     */
    public ?string $keyPrefix = null;

    /**
     * @var int Seconds to skip rate limiting after a storage failure
     */
    public int $pauseAfterFailure = 60;

    /** @var string|null The `keyPrefix` of the application cache, when the autodetected store is based on it */
    private ?string $cacheKeyPrefix = null;

    private ?string $resolvedKeyPrefix = null;

    /** @var int|null Until when rate limiting is paused in this request */
    private ?int $pausedUntil = null;

    /** @var array<string, true> Policies whose invalid windows were already logged in this request */
    private array $invalidLogged = [];

    public function getPolicy(string $name): ?array
    {
        $policy = $this->policies[$name] ?? null;
        if (!is_array($policy) || $policy === []) {
            return null;
        }

        $windows = [];
        foreach ($policy as $window => $values) {
            $limit = is_array($values) ? ($values['limit'] ?? null) : null;
            $span = is_array($values) ? ($values['span'] ?? null) : null;
            if (!is_int($limit) || !is_int($span) || $limit < 1 || $span < 1) {
                if (!isset($this->invalidLogged[$name])) {
                    Yii::error("Invalid rate limit window '$window' in policy '$name' ignored.", 'rateLimit');
                    $this->invalidLogged[$name] = true;
                }
                continue;
            }
            $windows[$window] = ['limit' => $limit, 'span' => $span];
        }

        return $windows ?: null;
    }

    public function getStorage(): StorageInterface
    {
        if ($this->storage instanceof StorageInterface) {
            return $this->storage;
        }

        if (is_array($this->storage)) {
            $storage = Yii::createObject($this->storage);
            if (!$storage instanceof StorageInterface) {
                throw new InvalidConfigException('The rate limit storage must implement StorageInterface.');
            }
            return $this->storage = $storage;
        }

        $cache = Yii::$app->cache;
        if ($cache instanceof RedisCache) {
            $this->cacheKeyPrefix = (string)$cache->keyPrefix;
            return $this->storage = new RedisStorage($cache->redis);
        }

        if (Yii::$app->has('redis')) {
            return $this->storage = new RedisStorage(Yii::$app->redis);
        }

        $this->cacheKeyPrefix = (string)$cache->keyPrefix;
        return $this->storage = new CacheStorage();
    }

    /**
     * Separates instances that share one Redis or cache, see [[$keyPrefix]].
     */
    public function getKeyPrefix(): string
    {
        if ($this->keyPrefix !== null && $this->keyPrefix !== '') {
            return $this->keyPrefix;
        }
        if ($this->resolvedKeyPrefix !== null) {
            return $this->resolvedKeyPrefix;
        }

        $this->getStorage();
        if ($this->cacheKeyPrefix !== null && $this->cacheKeyPrefix !== '') {
            return $this->resolvedKeyPrefix = $this->cacheKeyPrefix;
        }

        $secret = (string)Yii::$app->settings->get('secret');
        if ($secret !== '') {
            return $this->resolvedKeyPrefix = substr(hash('sha256', $secret), 0, 12);
        }

        return $this->resolvedKeyPrefix = (string)Yii::$app->id;
    }

    /**
     * Counts one request of $subject in the windows of the policy, shortest span first. Once a window
     * is exhausted, the longer ones are not charged for the refused request.
     *
     * @param string|null $context Appended to the log line of a refusal, e.g. `route 'api/v2/…'`
     */
    public function hit(string $policy, string $subject, ?string $context = null): RateLimitResult
    {
        $windows = $this->enabled ? $this->getPolicy($policy) : null;
        if ($windows === null) {
            return RateLimitResult::unlimited($policy);
        }

        // Counted by span; stable, so windows of one span keep their order. The headers keep the configured one.
        $ordered = $windows;
        uasort($ordered, fn($a, $b) => $a['span'] <=> $b['span']);

        $now = $this->now();
        $counters = [];

        try {
            $storage = $this->getStorage();
            if ($this->isPaused()) {
                return RateLimitResult::unlimited($policy);
            }

            $prefix = $this->getKeyPrefix();
            foreach ($ordered as $name => ['limit' => $limit, 'span' => $span]) {
                $start = intdiv($now, $span) * $span;
                $counters["rateLimit:$prefix:$policy:$name:$subject:$start"] = ['ttl' => $span, 'limit' => $limit, 'window' => $name, 'start' => $start];
            }

            $values = $storage->increment(array_map(fn($c) => ['ttl' => $c['ttl'], 'limit' => $c['limit']], $counters));
        } catch (\Throwable $e) {
            $this->pause($e);
            return RateLimitResult::unlimited($policy);
        }

        $deciding = null;
        foreach (array_values($counters) as $i => ['window' => $name, 'limit' => $limit, 'ttl' => $span, 'start' => $start]) {
            if (!isset($values[$i])) {
                break;
            }
            $count = $values[$i];

            if ($count === $limit + 1) {
                Yii::warning("Rate limit exceeded: policy '$policy', window '$name', subject '$subject'"
                    . ($context !== null ? ", $context" : '') . '.', 'rateLimit');
            }

            $candidate = [
                'window' => $name,
                'limit' => $limit,
                'span' => $span,
                'remaining' => max(0, $limit - $count),
                'resetsIn' => $start + $span - $now,
                'exhausted' => $count > $limit,
            ];

            if ($deciding === null || $this->decides($candidate, $deciding)) {
                $deciding = $candidate;
            }
        }

        if ($deciding === null) {
            return RateLimitResult::unlimited($policy);
        }

        return new RateLimitResult(
            !$deciding['exhausted'],
            $policy,
            $deciding['window'],
            $deciding['limit'],
            $deciding['remaining'],
            $deciding['resetsIn'],
            $deciding['exhausted'] ? $deciding['resetsIn'] : null,
            $windows,
        );
    }

    /**
     * Whether rate limiting is paused after a storage failure.
     */
    protected function isPaused(): bool
    {
        if ($this->pausedUntil !== null && $this->pausedUntil > $this->now()) {
            return true;
        }

        $cache = $this->getPauseCache();
        return $cache !== null && (int)$cache->get(self::PAUSE_KEY) > $this->now();
    }

    /**
     * Logs a storage failure and skips rate limiting for [[$pauseAfterFailure]] seconds: across requests
     * when the application cache is not the failing store, else for this request.
     */
    protected function pause(\Throwable $e): void
    {
        $until = $this->now() + $this->pauseAfterFailure;
        $this->pausedUntil = $until;

        $where = 'this request';
        $cache = $this->getPauseCache();
        try {
            if ($cache?->set(self::PAUSE_KEY, $until, $this->pauseAfterFailure)) {
                $where = "$this->pauseAfterFailure seconds";
            }
        } catch (\Throwable) {
        }

        Yii::error("Rate limit storage failed, rate limiting paused for $where: {$e->getMessage()}", 'rateLimit');
    }

    /**
     * The application cache, if it can keep the pause: it must not be the store (or share its Redis).
     */
    private function getPauseCache(): ?CacheInterface
    {
        if (!$this->storage instanceof StorageInterface || $this->storage instanceof CacheStorage) {
            return null;
        }

        $cache = Yii::$app->cache;
        return $cache instanceof RedisCache || $cache instanceof DummyCache ? null : $cache;
    }

    protected function now(): int
    {
        return time();
    }

    /**
     * The first exhausted window decides; else the one with the fewest remaining, then the shorter span.
     */
    private function decides(array $candidate, array $current): bool
    {
        if ($current['exhausted']) {
            return false;
        }
        if ($candidate['exhausted']) {
            return true;
        }

        return [$candidate['remaining'], $candidate['span']] < [$current['remaining'], $current['span']];
    }
}
