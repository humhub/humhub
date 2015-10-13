<?php

class m150204_103433_html5_notified extends EDbMigration
{
	public function up()
	{
	   if (HSetting::isInstalled()) {
            
            $this->insert('setting', array(
                'name' => 'enable_html5_desktop_notifications',
                'value' => 0,
                'module_id' => 'notification'
            ));
        }
        
        $this->addColumn('notification', 'desktop_notified', 'tinyint(1) DEFAULT 0');
        $this->update('notification', array('desktop_notified' => 1));
	}

	public function down()
	{
		echo "m150204_103433_html5_notified does not support migration down.\n";
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