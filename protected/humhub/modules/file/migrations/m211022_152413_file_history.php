<?php

use yii\db\Migration;

/**
 * Class m211022_152413_file_history
 */
class m211022_152413_file_history extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('file_history',
            [
                'id' => $this->primaryKey(),
                'file_id' => $this->integer()->notNull(),
                'size' => $this->bigInteger()->notNull(),
                'hash_sha1' => $this->string(40)->notNull(),
                'created_at' => $this->dateTime(),
                'created_by' => $this->integer(),
            ]);

        $this->addForeignKey('fk_file_history', 'file_history', 'file_id', 'file', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_file_history_user', 'file_history', 'created_by', 'user', 'id', 'SET NULL', 'SET NULL');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m211022_152413_file_history cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m211022_152413_file_history cannot be reverted.\n";

        return false;
    }
    */
}
