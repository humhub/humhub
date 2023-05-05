<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\services;

use Yii;

/**
 * Allow to know which users are currently online
 *
 * @since 1.15
 */
class IsOnlineService
{
    protected const CACHE_IS_ONLINE_PREFIX = 'is_online_user_id_';

    public int $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function updateStatus(): void
    {
        if ($this->userId) {
            Yii::$app->cache->set(self::CACHE_IS_ONLINE_PREFIX . $this->userId, true, 60); // Expires in 60 seconds
        }
    }

    public function getStatus(): bool
    {
        return $this->userId && (bool)Yii::$app->cache->get(self::CACHE_IS_ONLINE_PREFIX . $this->userId);
    }
}
