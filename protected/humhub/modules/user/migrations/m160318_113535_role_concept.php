<?php

use yii\db\Migration;

class m160318_113535_role_concept extends Migration
{
    public function up()
    {
		$this->createTable('sen_profile_field_group', array(
					'group_id' => 'int NOT NULL',
					'profile_field_id' => 'int NOT NULL',
						), '');
    }

    public function down()
    {
        echo "m160318_113535_role_concept cannot be reverted.\n";

        return false;
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
