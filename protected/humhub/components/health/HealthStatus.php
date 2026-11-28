<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\health;

/**
 * Health of a single check or of a whole report.
 *
 * Severity order: ok < warning < unknown < failure. The exit codes follow the Nagios plugin convention.
 *
 * @since 1.20
 */
enum HealthStatus: string
{
    case Ok = 'ok';
    case Warning = 'warning';
    case Unknown = 'unknown';
    case Failure = 'failure';

    public function severity(): int
    {
        return match ($this) {
            self::Ok => 0,
            self::Warning => 1,
            self::Unknown => 2,
            self::Failure => 3,
        };
    }

    public static function worst(self ...$statuses): self
    {
        $worst = self::Ok;
        foreach ($statuses as $status) {
            if ($status->severity() > $worst->severity()) {
                $worst = $status;
            }
        }

        return $worst;
    }

    public function exitCode(): int
    {
        return match ($this) {
            self::Ok => 0,
            self::Warning => 1,
            self::Failure => 2,
            self::Unknown => 3,
        };
    }
}
