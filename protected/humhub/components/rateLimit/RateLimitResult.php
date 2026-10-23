<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\rateLimit;

/**
 * Outcome of a [[RateLimiter::hit()]]: the state of the window that decides the response.
 *
 * @since 1.20
 */
final class RateLimitResult
{
    /**
     * @param array<string, array{int, int}> $windows All windows of the policy: name => [limit, seconds]
     */
    public function __construct(
        public readonly bool $allowed,
        public readonly string $policy,
        public readonly string $window = '',
        public readonly int $limit = 0,
        public readonly int $remaining = 0,
        public readonly int $resetsIn = 0,
        public readonly ?int $retryAfter = null,
        public readonly array $windows = [],
    ) {
    }

    public static function unlimited(string $policy): self
    {
        return new self(true, $policy);
    }

    /**
     * @return array<string, string> Response headers, empty for an unlimited result
     */
    public function toHeaders(): array
    {
        if ($this->window === '') {
            return [];
        }

        $parts = [];
        foreach ($this->windows as $name => [$limit, $span]) {
            $parts[] = sprintf('"%s";q=%d;w=%d', $name, $limit, $span);
        }

        $headers = [
            'RateLimit-Policy' => implode(', ', $parts),
            'RateLimit' => sprintf('"%s";r=%d;t=%d', $this->window, $this->remaining, $this->resetsIn),
        ];

        if (!$this->allowed) {
            $headers['Retry-After'] = (string)$this->retryAfter;
        }

        return $headers;
    }
}
