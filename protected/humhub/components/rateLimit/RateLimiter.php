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
use yii\redis\Cache as RedisCache;

/**
 * Counts hits of named policies per subject in fixed time windows.
 *
 * A policy is a list of windows (`name => [limit, seconds]`); a hit counts in every window
 * and is refused as soon as one of them is exhausted. A policy set to `false` is disabled.
 *
 * Config merging appends indexed arrays: a window given again in the user config arrives as
 * `[defaultLimit, defaultSpan, limit, span]`, so [[getPolicy()]] uses the last two values.
 *
 * See `docs/develop/concept-rate-limiting.md`.
 *
 * @since 1.20
 */
class RateLimiter extends Component
{
    public bool $enabled = true;

    /**
     * @var array<string, array<string, array{int, int}>|false> Policy name => windows, or false
     */
    public array $policies = [];

    /**
     * @var StorageInterface|array|null Instance or config; null selects the storage automatically
     */
    public $storage = null;

    public function getPolicy(string $name): ?array
    {
        $policy = $this->policies[$name] ?? null;
        if (!is_array($policy) || $policy === []) {
            return null;
        }

        $windows = [];
        foreach ($policy as $window => $values) {
            if (is_array($values) && count($values) >= 2) {
                $values = array_slice(array_values($values), -2);
                $windows[$window] = [(int)$values[0], (int)$values[1]];
            }
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

        if (Yii::$app->cache instanceof RedisCache) {
            return $this->storage = new RedisStorage(Yii::$app->cache->redis);
        }

        if (Yii::$app->has('redis')) {
            return $this->storage = new RedisStorage(Yii::$app->redis);
        }

        return $this->storage = new CacheStorage();
    }

    /**
     * Counts one request of $subject in every window of the policy.
     */
    public function hit(string $policy, string $subject): RateLimitResult
    {
        $windows = $this->enabled ? $this->getPolicy($policy) : null;
        if ($windows === null) {
            return RateLimitResult::unlimited($policy);
        }

        $now = $this->now();
        $deciding = null;
        $exhausted = false;

        try {
            $storage = $this->getStorage();
            foreach ($windows as $name => [$limit, $span]) {
                $start = intdiv($now, $span) * $span;
                $count = $storage->increment("rateLimit:$policy:$name:$subject:$start", $span);

                if ($count === $limit + 1) {
                    Yii::warning("Rate limit exceeded: policy '$policy', window '$name', subject '$subject'.", 'rateLimit');
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
        } catch (\Throwable $e) {
            Yii::error("Rate limit storage failed, request allowed: {$e->getMessage()}", 'rateLimit');
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
