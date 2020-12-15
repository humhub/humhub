<?php

use yii\db\Migration;

/**
 * Class m201214_194437_add_active_to_user_follow
 */
class m201214_194437_add_active_to_user_follow extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user_follow', 'active', 'tinyint(1) Default 1');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('user_follow', 'active');
        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m201214_194437_add_active_to_user_follow cannot be reverted.\n";

        return false;
    }
    */
}
