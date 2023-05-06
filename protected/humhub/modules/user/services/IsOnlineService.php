<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\services;

use humhub\modules\user\models\User;
use Yii;

/**
 * Allow to know which users are currently online
 *
 * @property-read string $cacheKey
 *
 * @since 1.15
 */
class IsOnlineService
{
    protected const CACHE_IS_ONLINE_PREFIX = 'is_online_user_id_';

    public ?User $user;

    public function __construct(?User $user)
    {
        $this->user = $user;
    }

    public function updateStatus(): void
    {
        if ($this->user && !Yii::$app->cache->exists($this->cacheKey)) {
            Yii::$app->cache->set($this->cacheKey, true, 60); // Expires in 60 seconds
        }
    }

    public function getStatus(): bool
    {
        return $this->user && Yii::$app->cache->exists($this->cacheKey);
    }

    protected function getCacheKey(): string
    {
        return self::CACHE_IS_ONLINE_PREFIX . $this->user->id;
    }
}
