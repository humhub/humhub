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
        $this->createTable('group_space', [
            'id' => 'pk',
            'space_id' => 'int(11) NOT NULL',
            'group_id' => 'int(11) NOT NULL',
        ], '');

        // Add indexes and foreign keys
        $this->createIndex('idx-group_space', 'group_space', ['space_id', 'group_id'], true);
        $this->addForeignKey('fk-group_space-space', 'group_space', 'space_id', 'space', 'id', 'CASCADE');
        $this->addForeignKey('fk-group_space-group', 'group_space', 'group_id', '`group`', 'id', 'CASCADE');

        //Old default group migration here.
        $rows = (new \yii\db\Query())
            ->select("*")
            ->from('group')
            ->where(['is not', 'group.space_id', new \yii\db\Expression('NULL')])
            ->all();
        foreach ($rows as $row) {

            $this->insert('group_space', [
                'space_id' => $row['space_id'],
                'group_id' => $row['id'],
            ]);
        }

        $columnData = $this
            ->getDb()
            ->getSchema()
            ->getTableSchema('group')
            ->getColumn('space_id');

        if ($columnData) {
            // Columns exists, do something!
            $this->dropForeignKey('fk_group-space_id', 'group');
            $this->dropColumn('group', 'space_id');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('group_space');
    }

}
