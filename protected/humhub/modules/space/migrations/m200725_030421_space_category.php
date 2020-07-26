<?php

use yii\db\Migration;

/**
 * Class m200725_030421_space_category
 */
class m200725_030421_space_category extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
      $this->addColumn("space", "parent_id", "int(11) NOT NULL");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200725_030421_space_category cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200725_030421_space_category cannot be reverted.\n";

        return false;
    }
    */
}
