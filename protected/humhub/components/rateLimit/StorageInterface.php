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
     * Increments the counters in the given order, in one round trip where the store allows it.
     *
     * A new counter expires after its `ttl` seconds. Incrementing stops after the first counter whose
     * new value exceeds its `limit`: the counters after it are left untouched and missing from the result.
     *
     * @param array<string, array{ttl: int, limit: int}> $counters key => ttl and limit
     * @return int[] the new values, in the order of $counters
     */
    public function increment(array $counters): array;
}
