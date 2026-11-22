<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\components;

use humhub\modules\oauth\events\DeviceConnectionsEvent;
use humhub\modules\oauth\Module;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Event;

/**
 * The device connections ([[DeviceConnection]]) modules offer a user, collected by
 * [[EVENT_COLLECT_CONNECTIONS]]:
 *
 * ```php
 * // config.php
 * [DeviceConnectionRegistry::class, DeviceConnectionRegistry::EVENT_COLLECT_CONNECTIONS, [Events::class, 'onCollectDeviceConnections']],
 *
 * // Events.php
 * public static function onCollectDeviceConnections(DeviceConnectionsEvent $event): void
 * {
 *     $event->connections[] = new DeviceConnection('cfiles-webdav', …, ['cfiles:read', 'cfiles:write'], [
 *         Yii::t('CfilesModule.base', 'Address') => …,
 *         Yii::t('CfilesModule.base', 'Username') => $event->user->username,
 *     ]);
 * }
 * ```
 *
 * There are none while personal access tokens are not allowed.
 *
 * @since 1.20
 */
final class DeviceConnectionRegistry
{
    public const EVENT_COLLECT_CONNECTIONS = 'collectConnections';

    /**
     * @return array<string, DeviceConnection> by id
     */
    public static function forUser(User $user): array
    {
        if (!Module::instance()->allowsPersonalAccessTokens()) {
            return [];
        }

        $event = new DeviceConnectionsEvent(['user' => $user]);
        Event::trigger(self::class, self::EVENT_COLLECT_CONNECTIONS, $event);

        $connections = [];
        foreach ($event->connections as $connection) {
            if (!$connection instanceof DeviceConnection) {
                Yii::warning('Ignored a device connection that is not a DeviceConnection object.', 'oauth');
                continue;
            }
            if (!preg_match(DeviceConnection::ID_PATTERN, $connection->id)) {
                Yii::warning('Ignored the device connection "' . $connection->id . '": an ID is lower-case words joined by hyphens.', 'oauth');
                continue;
            }
            if (isset($connections[$connection->id])) {
                Yii::warning('Ignored the duplicate device connection "' . $connection->id . '".', 'oauth');
                continue;
            }
            $unknown = array_filter($connection->scopes, static fn($scope) => !ScopeRegistry::has($scope));
            if ($connection->scopes === [] || $unknown !== []) {
                Yii::warning('Ignored the device connection "' . $connection->id . '": unknown scopes ' . implode(', ', $unknown) . '.', 'oauth');
                continue;
            }
            $connections[$connection->id] = $connection;
        }

        return $connections;
    }

    public static function find(User $user, string $id): ?DeviceConnection
    {
        return self::forUser($user)[$id] ?? null;
    }
}
