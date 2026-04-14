<?php


use humhub\components\Migration;

class m140930_210635_fix_default extends Migration
{
    public function up()
    {
        $this->safeAlterColumn('user', 'super_admin', "tinyint(4) NOT NULL DEFAULT '0'");
        $this->safeAlterColumn('user', 'language', "varchar(5) DEFAULT NULL");

        $this->safeAlterColumn('profile_field', 'sort_order', "int(11) NOT NULL DEFAULT '100'");
        $this->safeAlterColumn('profile_field', 'visible', "tinyint(4) NOT NULL DEFAULT '1'");

        $this->safeAlterColumn('profile_field_category', 'sort_order', "int(11) NOT NULL DEFAULT '100'");
        $this->safeAlterColumn('profile_field_category', 'visibility', "tinyint(4) NOT NULL DEFAULT '1'");

        $this->safeAlterColumn('user_content', 'updated_by', "int(11) DEFAULT NULL");
        $this->safeAlterColumn('user_content', 'created_by', "int(11) DEFAULT NULL");
    }

    public function down()
    {
        echo "m140930_210635_fix_default does not support migration down.\n";
        return false;
    }

    /*
    // Use safeUp/safeDown to do migration with transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
