<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

use humhub\components\Migration;
use humhub\modules\file\jobs\CollectFileMetadata;

/**
 * Collects the dimensions of the images stored before 1.20, in the background
 */
class m261127_100001_file_collect_metadata extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        try {
            Yii::$app->queue->push(new CollectFileMetadata());
        } catch (\Exception $ex) {
            Yii::error('Could not enqueue the file metadata job: ' . $ex->getMessage(), 'file');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m261127_100001_file_collect_metadata cannot be reverted.\n";

        return false;
    }
}
