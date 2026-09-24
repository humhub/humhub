<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity\live;

use humhub\modules\live\components\LiveEvent;

/**
 * Live event for new activities, consumed by the `ActivityBox` island.
 *
 * It says no more than "a container you follow has a new activity": the island reacts by
 * asking the API for the current head of its list and reconciling it (see `ActivityBox.vue`).
 * Deliberately so — an activity may have joined an existing group rather than started a new
 * entry, and only the server knows which. Nothing of the grouping travels here.
 *
 * Who receives it is decided by the live system from `contentContainerId` and `visibility`,
 * exactly like `content\live\NewContent`.
 *
 * @since 1.20
 */
class NewActivity extends LiveEvent
{
    /**
     * @var int id of the new activity record
     */
    public $activityId;

    /**
     * @var string|null guid of the container the activity belongs to, so a container-scoped box
     * can tell whether the event concerns it at all
     */
    public $containerGuid;
}
