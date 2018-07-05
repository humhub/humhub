<?php

use yii\db\Migration;

class m180305_084435_membership_pk extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk_space_membership-space_id', 'space_membership');
        $this->dropForeignKey('fk_space_membership-user_id', 'space_membership');
        $this->dropPrimaryKey('PRIMARY', 'space_membership');
        $this->addColumn('space_membership', 'id', $this->primaryKey());
        $this->addForeignKey(
            'fk_space_membership-space_id',
            'space_membership',
            'space_id',
            'space',
            'id',
            'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            'fk_space_membership-user_id',
            'space_membership',
            'user_id',
            'user',
            'id',
            'CASCADE', 'CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_space_membership-space_id', 'space_membership');
        $this->dropForeignKey('fk_space_membership-user_id', 'space_membership');
        $this->dropPrimaryKey('PRIMARY', 'space_membership');
        $this->addPrimaryKey('PRIMARY', 'space_membership', ['space_id', 'user_id']);
        $this->dropColumn('space_membership', 'id');
        $this->addForeignKey(
            'fk_space_membership-space_id',
            'space_membership',
            'space_id',
            'space',
            'id',
            'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            'fk_space_membership-user_id',
            'space_membership',
            'user_id',
            'user',
            'id',
            'CASCADE', 'CASCADE'
        );
    }
}
