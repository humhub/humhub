<?php

use humhub\components\Migration;
use humhub\modules\content\jobs\SearchRebuildIndex;
use humhub\modules\file\libs\FileHelper;

/**
 * Class m240203_112155_search
 */
class m240203_112155_search extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->safeCreateTable('content_fulltext', [
            'content_id' => $this->integer(),
            'contents' => $this->text(),
            'comments' => $this->text(),
            'files' => $this->text(),
        ]);

        $this->safeAddForeignKey('fk_content_fulltext', 'content_fulltext', 'content_id', 'content', 'id', 'CASCADE', 'CASCADE');

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
        echo "m240203_112155_search cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240203_112155_search cannot be reverted.\n";

        return false;
    }
    */
}
