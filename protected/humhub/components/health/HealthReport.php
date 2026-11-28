<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\health;

/**
 * The result of a health run: lifecycle state, the results of all checks run, and their renderings.
 *
 * @since 1.20
 */
final class HealthReport
{
    /**
     * Version of the output format. Bumped only for breaking changes (removed or renamed checks or
     * reserved fields, changed meaning of a value). New checks, fields and values do not bump it.
     */
    public const FORMAT = 1;

    /**
     * @param array<string, HealthResult> $results check id => result, in run order
     */
    public function __construct(
        public readonly HealthLevel $level,
        public readonly string $state,
        public readonly string $version,
        public readonly int $time,
        public readonly array $results,
    ) {
    }

    public function getStatus(): HealthStatus
    {
        return HealthStatus::worst(...array_map(fn(HealthResult $r) => $r->status, array_values($this->results)));
    }

    /**
     * @return string[] the outputs of all non-ok and all notable results
     */
    public function getNotes(): array
    {
        $notes = [];
        foreach ($this->results as $result) {
            if ($result->output !== null && ($result->status !== HealthStatus::Ok || $result->notable)) {
                $notes[] = $result->output;
            }
        }

        // The same reason from two checks (e.g. maintenance mode) is listed once
        return array_values(array_unique($notes));
    }

    public function toArray(bool $detailed = true): array
    {
        $data = [
            'format' => self::FORMAT,
            'status' => $this->getStatus()->value,
            'state' => $this->state,
        ];

        if (!$detailed) {
            return $data;
        }

        $checks = array_map(fn(HealthResult $r) => $r->toArray(), $this->results);
        // Check output may carry messages of external systems (e.g. a database error) that are not valid UTF-8
        array_walk_recursive($checks, function (&$value) {
            if (is_string($value)) {
                $value = mb_scrub($value, 'UTF-8');
            }
        });

        return $data + [
            'level' => $this->level->value,
            'version' => $this->version,
            'time' => gmdate('Y-m-d\TH:i:s\Z', $this->time),
            'notes' => array_map(fn(string $note) => mb_scrub($note, 'UTF-8'), $this->getNotes()),
            'checks' => $checks === [] ? new \stdClass() : $checks,
        ];
    }

    /**
     * The first line is valid Nagios plugin output.
     */
    public function toText(): string
    {
        $lines = [sprintf(
            '%s  %s  HumHub %s  (%s)',
            strtoupper($this->getStatus()->value),
            $this->state,
            $this->version,
            $this->level->value,
        )];

        $width = max([0, ...array_map('strlen', array_keys($this->results))]);
        foreach ($this->results as $id => $result) {
            $lines[] = rtrim(sprintf('  %-8s %-' . $width . 's  %s', $result->status->value, $id, $result->output ?? ''));
        }

        return implode("\n", $lines) . "\n";
    }

    /**
     * @param bool $failureOnly only 0 (ok, warning) and 1 (failure, unknown), as Docker's HEALTHCHECK requires
     */
    public function getExitCode(bool $failureOnly = false): int
    {
        $status = $this->getStatus();
        if ($failureOnly) {
            return in_array($status, [HealthStatus::Failure, HealthStatus::Unknown], true) ? 1 : 0;
        }

        return $status->exitCode();
    }

    public function getHttpStatusCode(): int
    {
        return in_array($this->getStatus(), [HealthStatus::Failure, HealthStatus::Unknown], true) ? 503 : 200;
    }
}
