<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\dav;

use humhub\modules\dav\components\LockBackend;
use Yii;
use yii\base\Event;
use yii\db\Expression;

/**
 * @since 1.20
 */
class Events
{
    /**
     * Removes the expired locks, and the clients' own files no request asked for in a while
     * (see `TemporaryFilePlugin`).
     */
    public static function onCronDailyRun(Event $event): void
    {
        Yii::$app->db->createCommand()
            ->delete(LockBackend::TABLE, ['<', new Expression('[[created]] + [[timeout]]'), time()])
            ->execute();

        $module = Module::instance();
        $path = $module->getTempPath();
        if (!is_dir($path)) {
            return;
        }

        $before = time() - $module->tempFileLifetime;
        foreach (glob($path . '/*/*.tempfile') ?: [] as $file) {
            if (is_file($file) && filemtime($file) < $before) {
                @unlink($file);
            }
        }
    }
}
