<?php

use yii\db\Migration;

/**
 * Class m211020_155002_file_deleted
 */
class m211020_155002_file_deleted extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('file', 'is_deleted', $this->boolean()->defaultValue(false)->after('size'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m211020_155002_file_deleted cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m211020_155002_file_deleted cannot be reverted.\n";

        return false;
    }
    */
}
