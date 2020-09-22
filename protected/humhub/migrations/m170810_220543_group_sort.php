<?php

use yii\db\Migration;

class m170810_220543_group_sort extends Migration
{
    public function safeUp()
    {
        $this->addColumn('group', 'sort_order', $this->integer()->defaultValue(100)->notNull());
    }

    public function safeDown()
    {
        echo "m170810_220543_group_sort cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m170810_220543_group_sort cannot be reverted.\n";

        return false;
    }
    */
}
