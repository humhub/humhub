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
    public function increment(array $counters): array
    {
        $cache = Yii::$app->cache;
        $values = [];
        foreach ($counters as $key => ['ttl' => $ttl, 'limit' => $limit]) {
            if ($cache->add($key, 1, $ttl)) {
                $value = 1;
            } else {
                $value = (int)$cache->get($key) + 1;
                $cache->set($key, $value, $ttl);
            }

            $values[] = $value;
            if ($value > $limit) {
                break;
            }
        }

        return $values;
    }
}
