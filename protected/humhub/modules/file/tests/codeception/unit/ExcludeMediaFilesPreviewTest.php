<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\file;

use humhub\modules\admin\models\forms\FileSettingsForm;
use humhub\modules\file\Module;
use m261127_100000_file_exclude_media_files_preview_on;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class ExcludeMediaFilesPreviewTest extends HumHubDbTestCase
{
    public function testIsOnWhenNeverSaved()
    {
        $this->getFileModule()->settings->delete('excludeMediaFilesPreview');

        $this->assertTrue($this->getFileModule()->getExcludeMediaFilesPreview());
    }

    public function testFollowsTheSavedValue()
    {
        $this->getFileModule()->settings->set('excludeMediaFilesPreview', '0');
        $this->assertFalse($this->getFileModule()->getExcludeMediaFilesPreview());

        $this->getFileModule()->settings->set('excludeMediaFilesPreview', '1');
        $this->assertTrue($this->getFileModule()->getExcludeMediaFilesPreview());
    }

    public function testSettingsFormLoadsAnUnsavedValueAsChecked()
    {
        $this->getFileModule()->settings->delete('excludeMediaFilesPreview');

        $this->assertSame(1, (new FileSettingsForm())->excludeMediaFilesPreview);
    }

    public function testMigrationSwitchesItOn()
    {
        $this->getFileModule()->settings->set('excludeMediaFilesPreview', '0');

        require_once Yii::getAlias('@humhub/modules/file/migrations/m261127_100000_file_exclude_media_files_preview_on.php');
        $migration = new m261127_100000_file_exclude_media_files_preview_on(['compact' => true]);
        $migration->safeUp();

        $this->assertTrue($this->getFileModule()->getExcludeMediaFilesPreview());
    }

    private function getFileModule(): Module
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('file');

        return $module;
    }
}
