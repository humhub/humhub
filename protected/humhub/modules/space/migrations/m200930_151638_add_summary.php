<?php

use yii\db\Migration;

/**
 * Class m200930_151638_add_summary
 */
class m200930_151638_add_summary extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('space', 'summary', 'text after description');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200930_151638_add_summary cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
    }
    public function down()
    {
        echo "m200930_151638_add_summary cannot be reverted.\n";
        return false;
    }
    */
}
