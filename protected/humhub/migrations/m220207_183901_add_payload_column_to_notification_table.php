<?php

use yii\db\Migration;

/**
 * Add `message` column to `notification` table
 */
class m220207_183901_add_payload_column_to_notification_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('notification', 'payload', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('notification', 'payload');
    }
}
