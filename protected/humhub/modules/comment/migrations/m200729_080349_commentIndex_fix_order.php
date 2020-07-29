<?php

use yii\db\Migration;

/**
 * Class m200729_080349_commentIndex_fix_order
 */
class m200729_080349_commentIndex_fix_order extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropIndex('idx_comment_target', 'comment');
        $this->createIndex('idx_comment_target', 'comment', ['object_id', 'object_model'], false);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200729_080349_commentIndex_fix_order cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200729_080349_commentIndex_fix_order cannot be reverted.\n";

        return false;
    }
    */
}
