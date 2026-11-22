<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\dav\events;

use humhub\modules\dav\components\Mount;
use humhub\modules\user\models\User;
use yii\base\Event;

/**
 * Collects the mounts modules offer, see `Module::EVENT_COLLECT_MOUNTS`.
 *
 * @since 1.20
 */
class MountsEvent extends Event
{
    /**
     * @var User the authenticated user of the request
     */
    public User $user;

    /**
     * @var Mount[]
     */
    public array $mounts = [];
}
