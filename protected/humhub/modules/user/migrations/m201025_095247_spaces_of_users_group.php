<?php

use yii\db\Migration;

/**
 * Class m201025_095247_spaces_of_users_group
 */
class m201025_095247_spaces_of_users_group extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('group_spaces', [
            'id' => 'pk',
            'space_id' => 'int(11) NOT NULL',
            'group_id' => 'int(11) NOT NULL',
        ], '');

        // Add indexes and foreign keys
        $this->createIndex('idx-group_spaces', 'group_spaces', ['space_id', 'group_id'], true);
        $this->addForeignKey('fk-group_spaces-space', 'group_spaces', 'space_id', 'space', 'id', 'CASCADE');
        $this->addForeignKey('fk-group_spaces-group', 'group_spaces', 'group_id', '`group`', 'id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('group_spaces');
    }

}
