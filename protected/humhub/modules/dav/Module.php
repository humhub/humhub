<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\dav;

use humhub\modules\dav\components\Mount;
use humhub\modules\dav\controllers\ServerController;
use humhub\modules\dav\events\MountsEvent;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Event;
use yii\helpers\Url;

/**
 * The WebDAV endpoint `/dav/`, see `docs/develop/concept-webdav.md`.
 *
 * Core serves the endpoint, the authentication and the locks; what it serves comes from the
 * modules. Each one mounts a collection under a name of its own in [[EVENT_COLLECT_MOUNTS]]:
 *
 * ```php
 * // a module's config.php
 * [\humhub\modules\dav\Module::class, \humhub\modules\dav\Module::EVENT_COLLECT_MOUNTS, [Events::class, 'onDavCollectMounts']],
 * ```
 *
 * @since 1.20
 */
class Module extends \humhub\components\Module
{
    public const EVENT_COLLECT_MOUNTS = 'collectMounts';

    /**
     * @var string where requests keep files for a while: what [[TemporaryFileFilterPlugin]]
     *      holds back from the modules (`.DS_Store`, `._*`, `~$*` …), per user
     */
    public string $tempPath = '@runtime/dav';

    /**
     * @var int seconds after which the daily cleanup removes such a file
     */
    public int $tempFileLifetime = 86400;

    public static function instance(): self
    {
        /** @var self $module */
        $module = Yii::$app->getModule('dav');
        return $module;
    }

    /**
     * The mounts the modules offer the user, by name. A mount whose name is invalid or taken
     * is ignored and logged.
     *
     * @return array<string, Mount>
     */
    public function getMounts(User $user): array
    {
        $event = new MountsEvent(['user' => $user]);
        Event::trigger(self::class, self::EVENT_COLLECT_MOUNTS, $event);

        $mounts = [];
        foreach ($event->mounts as $mount) {
            if (!$mount instanceof Mount) {
                Yii::warning('Ignored a WebDAV mount that is not a Mount object.', 'dav');
                continue;
            }
            if (!preg_match(Mount::NAME_PATTERN, $mount->name)) {
                Yii::warning('Ignored the WebDAV mount "' . $mount->name . '": a name is lower-case letters, digits and hyphens.', 'dav');
                continue;
            }
            if (isset($mounts[$mount->name])) {
                Yii::warning('Ignored the duplicate WebDAV mount "' . $mount->name . '".', 'dav');
                continue;
            }
            $mounts[$mount->name] = $mount;
        }

        return $mounts;
    }

    /**
     * The address a client connects a mount at, e.g. `https://example.org/dav/files/`; for the
     * fields of a device connection (`oauth\components\DeviceConnection`).
     */
    public function getMountUrl(string $name): string
    {
        return Url::to(ServerController::baseUri() . $name . '/', true);
    }

    /**
     * The folder for the temporary files of a user.
     */
    public function getTempPath(?int $userId = null): string
    {
        $path = rtrim(Yii::getAlias($this->tempPath), '/') . '/temp';

        return $userId === null ? $path : $path . '/' . $userId;
    }
}
