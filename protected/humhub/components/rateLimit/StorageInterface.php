<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\rateLimit;

/**
 * Counter store of the [[RateLimiter]].
 *
 * @since 1.20
 */
interface StorageInterface
{
    /**
     * Increments the counter and returns the new value; a new counter expires after $ttl seconds.
     */
    public function increment(string $key, int $ttl): int;
}
