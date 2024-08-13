<?php

use humhub\components\Migration;

/**
 * Class m210924_114847_container_blocked_users
 */
class m210924_114847_container_blocked_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->safeCreateTable('contentcontainer_blocked_users', [
            'contentcontainer_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
        ]);
        $this->safeAddPrimaryKey('pk-contentcontainer-blocked-users-rel', 'contentcontainer_blocked_users', ['contentcontainer_id', 'user_id']);
        $this->safeAddForeignKey('fk-contentcontainer-blocked-users-rel-contentcontainer-id', 'contentcontainer_blocked_users', 'contentcontainer_id', 'contentcontainer', 'id', 'CASCADE');
        $this->safeAddForeignKey('fk-contentcontainer-blocked-users-rel-user-id', 'contentcontainer_blocked_users', 'user_id', 'user', 'id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('contentcontainer_blocked_users');
    }

}
