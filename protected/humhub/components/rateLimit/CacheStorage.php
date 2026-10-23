<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\rateLimit;

use Yii;

/**
 * Best effort counting with the application cache, which has no atomic increment:
 * concurrent hits can be missed, which makes the limit looser, never stricter.
 *
 * @since 1.20
 */
class CacheStorage implements StorageInterface
{
    public function increment(string $key, int $ttl): int
    {
        $cache = Yii::$app->cache;
        if ($cache->add($key, 1, $ttl)) {
            return 1;
        }

        $value = (int)$cache->get($key) + 1;
        $cache->set($key, $value, $ttl);
        return $value;
    }
}
