<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\health;

use InvalidArgumentException;

/**
 * The immutable result of one health check.
 *
 * `$data` is check specific and rendered inline next to the reserved keys. `$output` is a short human readable
 * reason, mandatory for every status but ok. A notable ok result (e.g. "maintenance mode enabled") is listed in
 * the report's notes like a problem would be.
 *
 * @since 1.20
 */
final class HealthResult
{
    public const RESERVED_KEYS = ['status', 'output', 'durationMs', 'cachedAt'];

    public function __construct(
        public readonly HealthStatus $status,
        public readonly ?string $output = null,
        public readonly array $data = [],
        public readonly bool $notable = false,
        public readonly ?int $durationMs = null,
        public readonly ?string $cachedAt = null,
    ) {
        if ($status !== HealthStatus::Ok && ($output === null || $output === '')) {
            throw new InvalidArgumentException('A health result other than ok needs an output.');
        }

        $reserved = array_intersect(array_keys($data), self::RESERVED_KEYS);
        if ($reserved !== []) {
            throw new InvalidArgumentException('Reserved keys in health result data: ' . implode(', ', $reserved));
        }
    }

    public static function ok(?string $output = null, array $data = [], bool $notable = false): self
    {
        return new self(HealthStatus::Ok, $output, $data, $notable);
    }

    public static function warning(string $output, array $data = []): self
    {
        return new self(HealthStatus::Warning, $output, $data);
    }

    public static function failure(string $output, array $data = []): self
    {
        return new self(HealthStatus::Failure, $output, $data);
    }

    public static function unknown(string $output, array $data = []): self
    {
        return new self(HealthStatus::Unknown, $output, $data);
    }

    public function withStatus(HealthStatus $status, ?string $output = null): self
    {
        return new self($status, $output ?? $this->output, $this->data, $this->notable, $this->durationMs, $this->cachedAt);
    }

    public function withDuration(int $ms): self
    {
        return new self($this->status, $this->output, $this->data, $this->notable, $ms, $this->cachedAt);
    }

    public function withCachedAt(string $iso): self
    {
        return new self($this->status, $this->output, $this->data, $this->notable, $this->durationMs, $iso);
    }

    public function toArray(): array
    {
        $result = ['status' => $this->status->value];
        if ($this->output !== null) {
            $result['output'] = $this->output;
        }
        if ($this->durationMs !== null) {
            $result['durationMs'] = $this->durationMs;
        }
        if ($this->cachedAt !== null) {
            $result['cachedAt'] = $this->cachedAt;
        }

        return $result + $this->data;
    }
}
