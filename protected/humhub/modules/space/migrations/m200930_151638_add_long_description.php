<?php

use yii\db\Migration;

/**
 * Class m200930_151638_add_long_description
 */
class m200930_151638_add_long_description extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('space', 'long_description', 'text after description');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200930_151638_add_long_description cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200930_151638_add_long_description cannot be reverted.\n";

        return false;
    }
    */
}
