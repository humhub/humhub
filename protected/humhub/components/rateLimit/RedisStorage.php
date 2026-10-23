<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\rateLimit;

use yii\redis\ConnectionInterface;

/**
 * Atomic counting with Redis, shared by all nodes of a cluster: all counters of a hit in one script.
 *
 * @since 1.20
 */
class RedisStorage implements StorageInterface
{
    /**
     * KEYS are the counters, ARGV their ttl and limit in pairs. INCR and the first EXPIRE run in one
     * step, so a crash in between cannot leave a counter without expiry.
     */
    private const SCRIPT = <<<'LUA'
        local r = {}
        for i, key in ipairs(KEYS) do
            local v = redis.call('INCR', key)
            if v == 1 then redis.call('EXPIRE', key, ARGV[2 * i - 1]) end
            r[i] = v
            if v > tonumber(ARGV[2 * i]) then break end
        end
        return r
        LUA;

    public function __construct(private readonly ConnectionInterface $redis)
    {
    }

    public function increment(array $counters): array
    {
        if ($counters === []) {
            return [];
        }

        $args = [];
        foreach ($counters as ['ttl' => $ttl, 'limit' => $limit]) {
            $args[] = $ttl;
            $args[] = $limit;
        }

        $values = $this->redis->executeCommand('EVAL', [self::SCRIPT, count($counters), ...array_keys($counters), ...$args]);

        return array_map('intval', (array)$values);
    }
}
