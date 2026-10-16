<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity\events;

use humhub\modules\activity\components\BaseActivity;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\interfaces\ContentProvider;
use humhub\modules\user\models\User;
use yii\base\ModelEvent;

/**
 * Triggered by {@see BaseActivity::send()} before the activity is written, with the arguments of
 * the call. A handler may veto it with `isValid = false`:
 *
 * ```php
 * Event::on(ActivityManager::class, ActivityManager::EVENT_BEFORE_DISPATCH, function (BeforeDispatchEvent $event) {
 *     if ($event->class === LikeActivity::class) {
 *         $event->isValid = false;
 *     }
 * });
 * ```
 *
 * The arguments are for reading only: as up to 1.19, only `isValid` is taken back. For handlers
 * written for 1.19 they are also in `$data` (`['class' => …, 'target' => …, 'user' => …]`) - but
 * `Event::trigger()` replaces `$data` with the data a handler was attached with, so read the
 * properties.
 *
 * @api
 * @since 1.20
 */
final class BeforeDispatchEvent extends ModelEvent
{
    /**
     * @var class-string<BaseActivity> the class of the activity
     */
    public string $class;

    /**
     * @var ContentProvider|ContentContainerActiveRecord what the activity is about
     */
    public ContentProvider|ContentContainerActiveRecord $target;

    /**
     * @var User|null the user who caused the activity; `null` for the logged-in user
     */
    public ?User $user = null;
}
