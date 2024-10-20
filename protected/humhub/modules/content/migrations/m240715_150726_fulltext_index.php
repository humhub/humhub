<?php

use humhub\components\Migration;
use humhub\modules\content\jobs\SearchRebuildIndex;
use humhub\modules\file\libs\FileHelper;

/**
 * Class m240715_150726_fulltext_index
 */
class m240715_150726_fulltext_index extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Try to create the fulltext index again only if it was not created correctly in previous migration
        if ($this->indexExists('ftx', 'content_fulltext')) {
            return;
        }

        try {
            $this->execute('ALTER TABLE content_fulltext ADD FULLTEXT INDEX ftx (contents, comments, files)');

            Yii::$app->queue->push(new SearchRebuildIndex());

            FileHelper::removeDirectory(Yii::getAlias('@runtime/searchdb'));
        } catch (\Exception $ex) {
            Yii::error('Could not execute content fulltext search migration: ' . $ex->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240715_150726_fulltext_index cannot be reverted.\n";

        return false;
    }
}
