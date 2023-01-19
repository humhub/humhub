<?php


use yii\db\Migration;

class m150924_154635_user_invite_add_first_lastname extends Migration
{
    public function up()
    {
        $this->addColumn('user_invite', 'firstname', 'varchar(255)');
        $this->addColumn('user_invite', 'lastname', 'varchar(255)');
        
    }

    public function down()
    {
        echo "m150924_154635_user_add_imported_flag cannot be reverted.\n";

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
