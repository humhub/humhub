<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 * @noinspection PhpIllegalPsrClassPathInspection
 */

namespace tests\codeception\_support;

use humhub\components\rateLimit\StorageInterface;

/**
 * Rate limit counters of one application instance. The test suites start a new application per
 * test, so every test begins with empty counters, and no counts survive in the shared cache.
 *
 * @since 1.20
 */
class MemoryRateLimitStorage implements StorageInterface
{
    private array $counters = [];

    public function increment(array $counters): array
    {
        $values = [];
        foreach ($counters as $key => ['limit' => $limit]) {
            $values[] = $this->counters[$key] = ($this->counters[$key] ?? 0) + 1;
            if ($this->counters[$key] > $limit) {
                break;
            }
        }

        return $values;
    }
}
