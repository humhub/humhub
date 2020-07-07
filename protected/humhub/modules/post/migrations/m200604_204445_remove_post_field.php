<?php

use yii\db\Migration;

/**
 * Class m200604_204445_remove_post_field
 */
class m200604_204445_remove_post_field extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('post', 'message_2trash');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200604_204445_remove_post_field cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200604_204445_remove_post_field cannot be reverted.\n";

        return false;
    }
    */
}
