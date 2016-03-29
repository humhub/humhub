<?php

use yii\db\Migration;

class m160318_113535_role_concept extends Migration
{
    public function up()
    {
		$this->createTable('profile_field_assignment', array(
					'group_id' => 'int NOT NULL',
					'profile_field_id' => 'int NOT NULL',
						), '');
        $this->addPrimaryKey('pk_profile_field_assignment', 'profile_field_assignment', 'group_id,profile_field_id');
        
        $this->createTable('field_assignment_group', array(
					'field_assignment_group_id' => 'int NOT NULL',
					'name' => 'int NOT NULL',
						), '');
        $this->addPrimaryKey('pk_profile_field_assignment', 'field_assignment_group', 'field_assignment_group_id');
    }

    public function down()
    {
        echo "m160318_113535_role_concept cannot be reverted.\n";

        return false;        
        
        $this->dropTable('profile_field_assignment');
        $this->dropTable('field_assignment_group');
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}