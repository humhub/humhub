<?php

use yii\db\Migration;

/**
 * Add `message` column to `notification` table
 */
class m220207_183901_add_message_column_to_notification_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('notification', 'message', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('notification', 'message');
    }
}
