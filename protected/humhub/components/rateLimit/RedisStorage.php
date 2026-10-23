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
    /** INCR and the first EXPIRE in one step, so a crash in between cannot leave a counter without expiry. */
    private const SCRIPT = "local v = redis.call('INCR', KEYS[1]) if v == 1 then redis.call('EXPIRE', KEYS[1], ARGV[1]) end return v";

    public function __construct(private readonly ConnectionInterface $redis)
    {
    }

    public function increment(string $key, int $ttl): int
    {
        return (int)$this->redis->executeCommand('EVAL', [self::SCRIPT, 1, $key, $ttl]);
    }
}
