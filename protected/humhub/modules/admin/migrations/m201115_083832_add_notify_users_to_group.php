<?php

use yii\db\Migration;

/**
 * Class m201115_083832_add_notify_users_to_group
 */
class m201115_083832_add_notify_users_to_group extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('group', 'notify_users', $this->integer()->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('group','notify_users');
        return true;
    }


}
