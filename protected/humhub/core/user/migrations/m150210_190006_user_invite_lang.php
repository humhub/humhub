<?php

class m150210_190006_user_invite_lang extends EDbMigration
{
	public function up()
	{
	    $this->addColumn('user_invite', 'language', 'varchar(10) DEFAULT NULL');
	}

	public function down()
	{
		echo "m150210_190006_user_invite_lang does not support migration down.\n";
		return false;
	}

}