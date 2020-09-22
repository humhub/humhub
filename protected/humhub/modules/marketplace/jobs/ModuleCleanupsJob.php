<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\jobs;

use humhub\modules\file\libs\FileHelper;
use humhub\modules\marketplace\Module;
use humhub\modules\queue\ActiveJob;
use Yii;
use yii\base\ErrorException;

class ModuleCleanupsJob extends ActiveJob
{
    public $backupKeepTime = 60 * 60 * 24 * 14;
    public $downloadKeepTime = 60 * 60 * 24 * 30;

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->cleanupModuleBackups();
        $this->cleanupModuleDownloads();
    }

    /**
     * Cleanup downloaded module packages
     */
    private function cleanupModuleDownloads()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('marketplace');
        $moduleDownloadFolder = Yii::getAlias($module->modulesDownloadPath);

        if (!is_dir($moduleDownloadFolder)) {
            return;
        }

        foreach (scandir($moduleDownloadFolder) as $downloadFile) {
            $file = $moduleDownloadFolder . DIRECTORY_SEPARATOR . $downloadFile;
            if (is_file($file) && filemtime($file) + $this->downloadKeepTime < time()) {
                unlink($file);
            }
        }
    }

    /**
     * Cleanup module folder backups
     */
    private function cleanupModuleBackups()
    {
        $moduleBackupFolder = Yii::getAlias('@runtime/module_backups');

        if (!is_dir($moduleBackupFolder)) {
            return;
        }

        foreach (scandir($moduleBackupFolder) as $backup) {
            if (preg_match('/.*_(\d{8,})$/', $backup, $matches) && isset($matches[1])) {
                $backupDate = $matches[1];
                if ($backupDate + $this->backupKeepTime < time()) {
                    try {
                        FileHelper::removeDirectory($moduleBackupFolder . DIRECTORY_SEPARATOR . $backup);
                    } catch (ErrorException $e) {
                        Yii::error("Could not delete outdated backup: " . $moduleBackupFolder, 'marketplace');
                    }
                }
            }
        }

    }

}
