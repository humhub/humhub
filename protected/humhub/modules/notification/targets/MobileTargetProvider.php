<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\targets;

use humhub\modules\user\models\User;

/**
 * Sends the push messages of the {@see MobileTarget} - implemented by a push module (e.g.
 * fcm-push) and registered in the DI container under this interface.
 *
 * @api for push providers
 * @since 1.2, rewritten in 1.20
 */
interface MobileTargetProvider
{
    /**
     * Sends one push message for the batch to the recipient's devices: the text
     * {@see DeliveryBatch::getPushBody()}, the link {@see DeliveryBatch::getUrl()}, the
     * {@see DeliveryBatch::getCollapseKey()} that lets a newer message about the same
     * notification group replace an older one on the device, {@see DeliveryBatch::isHighPriority()}
     * as the message priority and the badge from {@see DeliveryBatch::getUnreadCount()}.
     *
     * @since 1.20
     */
    public function deliver(DeliveryBatch $batch): void;

    /**
     * Whether the provider can push to the given user (e.g. the user registered a device) - or,
     * without a user, whether it is set up at all.
     */
    public function isActive(?User $user = null): bool;
}
