<?php

use yii\db\Migration;

class m171027_220519_exclusive_jobs extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{queue_exclusive}}', [
            'id' => $this->string(50)->notNull(),
            'job_message_id' => $this->string(50),
            'job_status' => $this->smallInteger()->defaultValue(2),
            'last_update' => $this->timestamp()
        ]);
        $this->addPrimaryKey('pk_queue_exclusive', '{{queue_exclusive}}', 'id');
    }

    public function safeDown()
    {
        echo "m171027_220519_exclusive_jobs cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171027_220519_exclusive_jobs cannot be reverted.\n";

        return false;
    }
    */
}
