<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\Migration;

/**
 * Creates `notification_delivery`: one row per notification and channel (e-mail, mobile, a
 * module's) that a delivery is scheduled for, see
 * {@see \humhub\modules\notification\services\DeliveryScheduler}.
 *
 * @since 1.20
 */
class m261007_100000_delivery extends Migration
{
    public function safeUp()
    {
        $this->safeCreateTable('notification_delivery', [
            'id' => $this->primaryKey(),
            'notification_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'channel' => $this->string(32)->notNull(),
            'state' => $this->tinyInteger()->notNull()->defaultValue(0),
            'due_at' => $this->dateTime()->notNull(),
            'sent_at' => $this->dateTime()->null(),
            'created_at' => $this->dateTime()->notNull(),
        ]);

        $this->safeAddForeignKey('fk_notification_delivery_notification', 'notification_delivery', 'notification_id', 'notification', 'id', 'CASCADE', 'CASCADE');
        $this->safeAddForeignKey('fk_notification_delivery_user', 'notification_delivery', 'user_id', 'user', 'id', 'CASCADE', 'CASCADE');
        $this->safeCreateIndex('idx_notification_delivery_due', 'notification_delivery', ['user_id', 'channel', 'state', 'due_at']);
        $this->safeCreateIndex('idx_notification_delivery_notification', 'notification_delivery', ['notification_id']);
        // for DeliveryService::sweep(): overdue pending rows, old finished rows
        $this->safeCreateIndex('idx_notification_delivery_state_due', 'notification_delivery', ['state', 'due_at']);
        $this->safeCreateIndex('idx_notification_delivery_state_created', 'notification_delivery', ['state', 'created_at']);
    }

    public function safeDown()
    {
        $this->safeDropTable('notification_delivery');
    }
}
