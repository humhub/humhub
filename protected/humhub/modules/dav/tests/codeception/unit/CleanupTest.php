<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\dav\tests\codeception\unit;

use humhub\modules\dav\Events;
use humhub\modules\dav\Module;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\Event;
use yii\db\Query;
use yii\helpers\FileHelper;

class CleanupTest extends HumHubDbTestCase
{
    protected function _after()
    {
        Module::instance()->tempPath = '@runtime/dav';
        parent::_after();
    }

    public function testTheDailyRunRemovesExpiredLocksAndOldTemporaryFiles()
    {
        $db = Yii::$app->db;
        $db->createCommand()->insert('dav_lock', ['token' => 'expired', 'uri' => 'a', 'created' => time() - 3600, 'timeout' => 1800])->execute();
        $db->createCommand()->insert('dav_lock', ['token' => 'valid', 'uri' => 'b', 'created' => time() - 60, 'timeout' => 1800])->execute();

        $module = Module::instance();
        $module->tempPath = Yii::getAlias('@runtime') . '/dav-cleanup-' . uniqid();
        FileHelper::createDirectory($module->getTempPath(2));
        $old = $module->getTempPath(2) . '/sabredav_old.tempfile';
        $new = $module->getTempPath(2) . '/sabredav_new.tempfile';
        file_put_contents($old, 'x');
        file_put_contents($new, 'x');
        touch($old, time() - $module->tempFileLifetime - 60);

        Events::onCronDailyRun(new Event());

        $this->assertSame(['valid'], (new Query())->select('token')->from('dav_lock')->column());
        $this->assertFileDoesNotExist($old);
        $this->assertFileExists($new);
        FileHelper::removeDirectory($module->tempPath);
    }
}
