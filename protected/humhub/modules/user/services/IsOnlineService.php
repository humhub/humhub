<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\services;

use humhub\modules\user\models\User;
use humhub\modules\user\Module;
use Yii;

/**
 * Allow to know which users are currently online
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
        if ($this->isEnabled() && $this->getStatus() === false) {
            Yii::$app->cache->set($this->getCacheKey(), true, 60); // Expires in 60 seconds
        }
    }

    public function getStatus(): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $cacheValue = Yii::$app->cache->get($this->getCacheKey());
        return $cacheValue !== false; // Return true if key exists and is not `false`
    }

    public function isEnabled(): bool
    {
        if (!$this->user) {
            return false;
        }

        /* @var $module Module */
        $module = Yii::$app->getModule('user');
        $settingsManager = $module->settings;

        return
            !$settingsManager->get('auth.hideOnlineStatus')
            && !$this->user->settings->get('hideOnlineStatus');
    }

    protected function getCacheKey(): string
    {
        return self::CACHE_IS_ONLINE_PREFIX . $this->user->id;
    }
}
