<?php

use humhub\components\Migration;

class m251007_133345_fulltext_index_primary_key extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->safeDropForeignKey('fk_content_fulltext', 'content_fulltext');
        $this->safeDropIndex('fk_content_fulltext', 'content_fulltext');
        $this->safeAddPrimaryKey(null, 'content_fulltext', 'content_id');
        $this->safeAddForeignKey('fk_content_fulltext', 'content_fulltext', 'content_id', 'content', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m251007_133345_fulltext_index_primary_key cannot be reverted.\n";

        return false;
    }
}
