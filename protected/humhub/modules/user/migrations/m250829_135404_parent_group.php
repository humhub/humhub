<?php

use humhub\components\Migration;

class m250829_135404_parent_group extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->safeAddColumn('group', 'parent_group_id', $this->integer()->null()->after('description'));
        $this->safeCreateIndex('idx-group-parent_group_id', 'group', 'parent_group_id');
        $this->safeAddForeignKey('fk-group-parent_group_id', 'group', 'parent_group_id', 'group', 'id', 'SET NULL', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->safeDropForeignKey('fk-group-parent_group_id', 'group');
        $this->safeDropColumn('group', 'parent_group_id');
    }
}
