<?php

use yii\db\Migration;

/**
 * Class m200715_184207_commentIndex
 */
class m200715_184207_commentIndex extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Was only used in beta version, avoid double index creation in stable version update
        //$this->createIndex('idx_comment_target', 'comment', ['object_model', 'object_id'], false);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200715_184207_commentIndex cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200715_184207_commentIndex cannot be reverted.\n";

        return false;
    }
    */
}
