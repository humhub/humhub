<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\rateLimit;

use yii\redis\ConnectionInterface;

/**
 * Atomic counting with Redis, shared by all nodes of a cluster.
 *
 * @since 1.20
 */
class RedisStorage implements StorageInterface
{
    public function __construct(private readonly ConnectionInterface $redis)
    {
    }

    public function increment(string $key, int $ttl): int
    {
        $value = (int)$this->redis->executeCommand('INCR', [$key]);
        if ($value === 1) {
            $this->redis->executeCommand('EXPIRE', [$key, $ttl]);
        }
        return $value;
    }
}
