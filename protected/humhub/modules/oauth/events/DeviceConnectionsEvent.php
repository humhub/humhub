<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\events;

use humhub\modules\oauth\components\DeviceConnection;
use humhub\modules\user\models\User;
use yii\base\Event;

/**
 * Collects the connections modules offer a user for a device, see
 * `DeviceConnectionRegistry::EVENT_COLLECT_CONNECTIONS`.
 *
 * @since 1.20
 */
class DeviceConnectionsEvent extends Event
{
    public User $user;

    /**
     * @var DeviceConnection[]
     */
    public array $connections = [];
}
