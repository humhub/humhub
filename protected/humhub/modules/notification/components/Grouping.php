<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\components;

use humhub\modules\notification\services\GroupingService;

/**
 * Which notifications of a recipient are shown as one entry ("Anna, Ben and 3 more like …"), see
 * {@see BaseNotification::grouping()}.
 *
 * A notification is grouped with the recipient's other notifications of the same class that
 * match it in what the grouping names and were created in the same time bucket, once there are at
 * least {@see withThreshold() threshold} of them.
 *
 * The factory names what must be the same and present - a notification without it (e.g. without a
 * content for {@see byContent()}) is not grouped. The `and…()` modifiers add further columns that
 * must be the same, an empty value matching an empty one. Every method returns a new instance.
 *
 * ```php
 * public static function grouping(): ?Grouping
 * {
 *     return Grouping::byContent()->andSource();  // e.g. the likes of one content or one comment
 * }
 * ```
 *
 * @api
 * @since 1.20
 */
final readonly class Grouping
{
    /**
     * @param string[] $required the columns that must be the same and not empty
     * @param string[] $matched the columns that must be the same, empty matching empty
     */
    private function __construct(
        private array $required,
        private array $matched = [],
        private bool $unseenOnly = false,
        private int $threshold = 2,
        private int $timeBucket = 900,
    ) {
    }

    /**
     * The notifications about the same content (also those about its comments and other content addons).
     */
    public static function byContent(): self
    {
        return new self([GroupingService::CONTENT]);
    }

    /**
     * The notifications about the same source record - a content addon (e.g. a comment) or another
     * record a notification is sent about, not a content or a container.
     */
    public static function bySource(): self
    {
        return new self([GroupingService::SOURCE]);
    }

    /**
     * The notifications about the same content container (a space or a profile).
     */
    public static function byContainer(): self
    {
        return new self([GroupingService::CONTAINER]);
    }

    /**
     * All notifications of the class, e.g. "Anna, Ben and 3 more follow you".
     */
    public static function byClass(): self
    {
        return new self([]);
    }

    /**
     * Also the same source record, see {@see bySource()}; none matches none.
     */
    public function andSource(): self
    {
        return $this->with(matched: GroupingService::SOURCE);
    }

    /**
     * Also the same content, see {@see byContent()}; none matches none.
     */
    public function andContent(): self
    {
        return $this->with(matched: GroupingService::CONTENT);
    }

    /**
     * Also the same originator; none matches none.
     */
    public function andOriginator(): self
    {
        return $this->with(matched: GroupingService::ORIGINATOR);
    }

    /**
     * Also a content of the same type (e.g. posts); a notification without a content is not grouped.
     */
    public function andContentType(): self
    {
        return $this->with(matched: GroupingService::CONTENT_TYPE);
    }

    /**
     * Only unseen notifications: a new notification after the recipient saw the group starts a new one.
     */
    public function unseenOnly(): self
    {
        return new self($this->required, $this->matched, true, $this->threshold, $this->timeBucket);
    }

    /**
     * The minimum number of notifications that form a group, 2 by default.
     */
    public function withThreshold(int $threshold): self
    {
        if ($threshold < 2) {
            throw new \InvalidArgumentException('The grouping threshold must be at least 2.');
        }

        return new self($this->required, $this->matched, $this->unseenOnly, $threshold, $this->timeBucket);
    }

    /**
     * The length of the time buckets in seconds, 900 (15 minutes) by default: only notifications
     * created in the same bucket are grouped.
     */
    public function withTimeBucket(int $seconds): self
    {
        if ($seconds <= 0) {
            throw new \InvalidArgumentException('The grouping time bucket must be greater than 0.');
        }

        return new self($this->required, $this->matched, $this->unseenOnly, $this->threshold, $seconds);
    }

    /**
     * @return string[] the columns that must be the same and present, see {@see GroupingService}
     * @internal
     */
    public function getRequired(): array
    {
        return $this->required;
    }

    /**
     * @return string[] the columns that must be the same, empty matching empty, see {@see GroupingService}
     * @internal
     */
    public function getMatched(): array
    {
        return $this->matched;
    }

    /**
     * @internal
     */
    public function isUnseenOnly(): bool
    {
        return $this->unseenOnly;
    }

    /**
     * @internal
     */
    public function getThreshold(): int
    {
        return $this->threshold;
    }

    /**
     * @internal
     */
    public function getTimeBucket(): int
    {
        return $this->timeBucket;
    }

    private function with(string $matched): self
    {
        return new self(
            $this->required,
            array_values(array_unique([...$this->matched, $matched])),
            $this->unseenOnly,
            $this->threshold,
            $this->timeBucket,
        );
    }
}
