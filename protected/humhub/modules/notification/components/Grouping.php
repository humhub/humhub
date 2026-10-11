<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\components;

/**
 * Which records of a class are shown as one entry ("Anna, Ben and 3 more like …"), declared by
 * `grouping()` of a notification class ({@see \humhub\modules\notification\components\BaseNotification::grouping()})
 * or an activity class ({@see \humhub\modules\activity\components\BaseActivity::grouping()}).
 *
 * A record is grouped with the other records of the same class (of the same recipient, for a
 * notification) that match it in what the grouping names and were created in the same time
 * bucket, once there are at least {@see withThreshold() threshold} of them.
 *
 * The factory names what must be the same and present - a record without it (e.g. without a
 * content for {@see byContent()}) is not grouped. The `and…()` modifiers add further things that
 * must be the same, an empty value matching an empty one. Every method returns a new instance.
 *
 * ```php
 * public static function grouping(): ?Grouping
 * {
 *     return Grouping::byContent()->andSource();  // e.g. the likes of one content or one comment
 * }
 * ```
 *
 * The value object only names what is compared; the grouping service of the notification and of
 * the activity module translates it to their table. A modifier that has no meaning there is
 * rejected with an `InvalidConfigException` when a record of the class is grouped:
 * {@see unseenOnly()} for activities (they are not seen by anybody), {@see andSourceParent()} for
 * notifications.
 *
 * @api
 * @since 1.20
 */
final readonly class Grouping
{
    /**
     * The things a grouping compares; each grouping service maps them to its columns.
     *
     * @internal
     */
    public const CONTENT = 'content';
    /**
     * @internal
     */
    public const SOURCE = 'source';
    /**
     * @internal
     */
    public const SOURCE_PARENT = 'sourceParent';
    /**
     * @internal
     */
    public const CONTAINER = 'container';
    /**
     * @internal
     */
    public const ORIGINATOR = 'originator';
    /**
     * @internal
     */
    public const CONTENT_TYPE = 'contentType';
    /**
     * @internal
     */
    public const CONTENT_VISIBILITY = 'contentVisibility';

    /**
     * @param string[] $required what must be the same and not empty
     * @param string[] $matched what must be the same, empty matching empty
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
     * The records about the same content (also those about its comments and other content addons).
     *
     * @api
     */
    public static function byContent(): self
    {
        return new self([self::CONTENT]);
    }

    /**
     * The records about the same source record - a content addon (e.g. a comment, or the like of
     * a like activity) or another record a notification is sent about, not a content or a container.
     *
     * @api
     */
    public static function bySource(): self
    {
        return new self([self::SOURCE]);
    }

    /**
     * The records about the same content container (a space or a profile).
     *
     * @api
     */
    public static function byContainer(): self
    {
        return new self([self::CONTAINER]);
    }

    /**
     * All records of the class, e.g. "Anna, Ben and 3 more follow you".
     *
     * @api
     */
    public static function byClass(): self
    {
        return new self([]);
    }

    /**
     * Also the same source record, see {@see bySource()}; none matches none.
     *
     * @api
     */
    public function andSource(): self
    {
        return $this->with(self::SOURCE);
    }

    /**
     * Also the same parent of the source record: the content addon the source belongs to, e.g.
     * the comment a like is about; none matches none (e.g. the likes of the content itself).
     * Activities only.
     *
     * @api
     */
    public function andSourceParent(): self
    {
        return $this->with(self::SOURCE_PARENT);
    }

    /**
     * Also the same content, see {@see byContent()}; none matches none.
     *
     * @api
     */
    public function andContent(): self
    {
        return $this->with(self::CONTENT);
    }

    /**
     * Also the same content container, see {@see byContainer()}; none matches none.
     *
     * @api
     */
    public function andContainer(): self
    {
        return $this->with(self::CONTAINER);
    }

    /**
     * Also the same originator (the user who caused the record); none matches none.
     *
     * @api
     */
    public function andOriginator(): self
    {
        return $this->with(self::ORIGINATOR);
    }

    /**
     * Also a content of the same type (e.g. posts); a record without a content is not grouped.
     *
     * @api
     */
    public function andContentType(): self
    {
        return $this->with(self::CONTENT_TYPE);
    }

    /**
     * Also a content of the same visibility (public or private); a record without a content is
     * not grouped.
     *
     * @api
     */
    public function andContentVisibility(): self
    {
        return $this->with(self::CONTENT_VISIBILITY);
    }

    /**
     * Only unseen records: a new notification after the recipient saw the group starts a new one.
     * Notifications only.
     *
     * @api
     */
    public function unseenOnly(): self
    {
        return new self($this->required, $this->matched, true, $this->threshold, $this->timeBucket);
    }

    /**
     * The minimum number of records that form a group, 2 by default.
     *
     * @api
     */
    public function withThreshold(int $threshold): self
    {
        if ($threshold < 2) {
            throw new \InvalidArgumentException('The grouping threshold must be at least 2.');
        }

        return new self($this->required, $this->matched, $this->unseenOnly, $threshold, $this->timeBucket);
    }

    /**
     * The length of the time buckets in seconds, 900 (15 minutes) by default: only records
     * created in the same bucket are grouped.
     *
     * @api
     */
    public function withTimeBucket(int $seconds): self
    {
        if ($seconds <= 0) {
            throw new \InvalidArgumentException('The grouping time bucket must be greater than 0.');
        }

        return new self($this->required, $this->matched, $this->unseenOnly, $this->threshold, $seconds);
    }

    /**
     * @return string[] what must be the same and present, see the `CONTENT`, `SOURCE`, … constants
     * @internal read by the grouping services
     */
    public function getRequired(): array
    {
        return $this->required;
    }

    /**
     * @return string[] what must be the same, empty matching empty, see the `CONTENT`, `SOURCE`, … constants
     * @internal read by the grouping services
     */
    public function getMatched(): array
    {
        return $this->matched;
    }

    /**
     * @internal read by the grouping services
     */
    public function isUnseenOnly(): bool
    {
        return $this->unseenOnly;
    }

    /**
     * @internal read by the grouping services
     */
    public function getThreshold(): int
    {
        return $this->threshold;
    }

    /**
     * @internal read by the grouping services
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
