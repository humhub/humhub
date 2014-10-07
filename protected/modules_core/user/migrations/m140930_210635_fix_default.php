<?php

class m140930_210635_fix_default extends EDbMigration
{
	public function up()
	{
            $this->alterColumn('user', 'super_admin', "tinyint(4) NOT NULL DEFAULT '0'");
            $this->alterColumn('user', 'language', "varchar(5) DEFAULT NULL");
 
            $this->alterColumn('profile_field', 'sort_order', "int(11) NOT NULL DEFAULT '100'");
            $this->alterColumn('profile_field', 'visible', "tinyint(4) NOT NULL DEFAULT '1'");

            $this->alterColumn('profile_field_category', 'sort_order', "int(11) NOT NULL DEFAULT '100'");
            $this->alterColumn('profile_field_category', 'visibility', "tinyint(4) NOT NULL DEFAULT '1'");
            
            $this->alterColumn('user_content', 'updated_by', "int(11) DEFAULT NULL");
            $this->alterColumn('user_content', 'created_by', "int(11) DEFAULT NULL");
            
            
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