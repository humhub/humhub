<?php

use yii\db\Migration;

/**
 * Class m200930_151639_add_about
 */
class m200930_151639_add_about extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('space', 'about', 'text after description');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200930_151639_add_about cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
    }
    public function down()
    {
        echo "m200930_151639_add_about cannot be reverted.\n";
        return false;
    }
    */
}
