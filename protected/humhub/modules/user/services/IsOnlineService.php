<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\services;

use humhub\modules\content\models\ContentContainerSetting;
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

    public function __construct(public ?User $user)
    {
    }

    public function updateStatus(): void
    {
        if ($this->isEnabled() && !Yii::$app->cache->exists($this->getCacheKey())) {
            Yii::$app->cache->set($this->getCacheKey(), true, 60); // Expires in 60 seconds
        }
    }

    public function getStatus(): bool
    {
        return
            $this->isEnabled()
            && Yii::$app->cache->exists($this->getCacheKey());
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

    /**
     * The online status of several users at once — {@see getStatus()} where {@see isEnabled()},
     * `null` where it is not (the administrator hides the online status, or the user hides
     * their own) — with one settings query and one cache read for all of them, not a number
     * per user.
     *
     * @param User[] $users
     * @return array<int, bool|null> by user id
     * @since 1.20
     */
    public static function getStatuses(array $users): array
    {
        if ($users === []) {
            return [];
        }

        /* @var $module Module */
        $module = Yii::$app->getModule('user');
        if ($module->settings->get('auth.hideOnlineStatus')) {
            return array_fill_keys(array_map(static fn(User $user) => (int)$user->id, $users), null);
        }

        // What `$user->settings->get('hideOnlineStatus')` reads, for all users in one query.
        $hidden = [];
        foreach (ContentContainerSetting::find()
            ->select(['contentcontainer_id', 'value'])
            ->where([
                'module_id' => 'user',
                'name' => 'hideOnlineStatus',
                'contentcontainer_id' => array_map(static fn(User $user) => $user->contentcontainer_id, $users),
            ])
            ->asArray()
            ->all() as $setting) {
            if ($setting['value']) {
                $hidden[(int)$setting['contentcontainer_id']] = true;
            }
        }

        $keys = array_map(static fn(User $user) => self::CACHE_IS_ONLINE_PREFIX . $user->id, $users);
        $online = Yii::$app->cache->multiGet($keys);

        $statuses = [];
        foreach ($users as $index => $user) {
            $statuses[(int)$user->id] = isset($hidden[(int)$user->contentcontainer_id])
                ? null
                : ($online[$keys[$index]] ?? false) !== false;
        }

        return $statuses;
    }

    protected function getCacheKey(): string
    {
        return self::CACHE_IS_ONLINE_PREFIX . $this->user->id;
    }
}
