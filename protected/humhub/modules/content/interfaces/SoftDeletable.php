<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\interfaces;

/**
 * Interface for classes which are deletable softly.
 *
 * @see \humhub\modules\content\models\Content
 * @since 1.14
 */
interface SoftDeletable
{
    /**
     * @event ModelEvent an event that is triggered before soft deleting a record.
     * You may set [[ModelEvent::isValid]] to be `false` to stop the deletion.
     */
    const EVENT_BEFORE_SOFT_DELETE = 'beforeSoftDelete';

    /**
     * @event Event an event that is triggered after a record is deleted softly.
     */
    const EVENT_AFTER_SOFT_DELETE = 'afterSoftDelete';

    /**
     * This method is invoked before soft deleting a record.
     *
     * The default implementation raises the [[EVENT_BEFORE_SOFT_DELETE]] event.
     *
     * @return bool whether the record should be deleted. Defaults to `true`.
     * @since 1.14
     */
    public function beforeSoftDelete(): bool;

    /**
     * Marks the record as deleted.
     *
     * Content which are marked as deleted will not longer returned in queries/stream/search.
     * A cron job will remove these content permanently.
     * If installed, such content can also be restored using the `recycle-bin` module.
     *
     * @return bool
     * @since 1.14
     */
    public function softDelete(): bool;

    /**
     * This method is invoked after soft deleting a record.
     * The default implementation raises the [[EVENT_AFTER_SOFT_DELETE]] event.
     * @since 1.14
     */
    public function afterSoftDelete();

    /**
     * Deletes this content record immediately and permanently
     *
     * @return bool
     * @since 1.14
     */
    public function hardDelete(): bool;
}