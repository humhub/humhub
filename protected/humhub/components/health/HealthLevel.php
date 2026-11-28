<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\health;

/**
 * The question a health report answers. Levels are cumulative: `ready` includes `live`, `all` includes `ready`.
 *
 * - `live`: is the process broken so that a restart helps? Never depends on the database.
 * - `ready`: can this instance serve users now?
 * - `all`: is everything in order? For monitoring only.
 *
 * @since 1.20
 */
enum HealthLevel: string
{
    case Live = 'live';
    case Ready = 'ready';
    case All = 'all';

    /**
     * @return bool whether a check of level $checkLevel runs when this level is requested
     */
    public function includes(self $checkLevel): bool
    {
        return $checkLevel->rank() <= $this->rank();
    }

    private function rank(): int
    {
        return match ($this) {
            self::Live => 0,
            self::Ready => 1,
            self::All => 2,
        };
    }
}
