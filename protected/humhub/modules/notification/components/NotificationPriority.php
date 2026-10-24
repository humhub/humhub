<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\components;

/**
 * How urgently the delivery layer treats a notification.
 *
 * - High: goes out at once.
 * - Normal: follows the channel's adaptive delay.
 * - Low: waits for the next mail that goes out anyway.
 *
 * The enum is int-backed so the delivery layer can compare priorities by `->value`.
 *
 * @since 1.20
 */
enum NotificationPriority: int
{
    case Low = 0;
    case Normal = 1;
    case High = 2;
}
