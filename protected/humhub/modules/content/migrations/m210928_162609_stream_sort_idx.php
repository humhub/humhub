<?php

use yii\db\Migration;

/**
 * Class m210928_162609_stream_sort_idx
 */
class m210928_162609_stream_sort_idx extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('idx_stream_created', 'content', 'created_at', false);
        $this->createIndex('idx_stream_updated', 'content', 'stream_sort_date', false);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210928_162609_stream_sort_idx cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210928_162609_stream_sort_idx cannot be reverted.\n";

        return false;
    }
    */
}
