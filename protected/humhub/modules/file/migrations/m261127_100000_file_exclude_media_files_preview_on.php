<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

use humhub\components\Migration;

/**
 * Leaves media files out of the attachment list everywhere: the setting had no effect from 1.18 until 1.20, the
 * new image area would show them twice
 */
class m261127_100000_file_exclude_media_files_preview_on extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        Yii::$app->getModule('file')->settings->set('excludeMediaFilesPreview', 1);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m261127_100000_file_exclude_media_files_preview_on cannot be reverted.\n";

        return false;
    }
}
