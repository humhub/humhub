<?php

use yii\db\Migration;

class m170112_115052_settings extends Migration
{

    public function up()
    {
        /**
         * Update old interval setting key names
         */
        $this->update('setting', ['name' => 'mailSummaryInterval'], ['name' => 'receive_email_activities', 'module_id' => 'activity']);
        $this->update('setting', ['value' => 5], ['name' => 'mailSummaryInterval', 'value' => 1]);
        $this->update('setting', ['value' => 1], ['name' => 'mailSummaryInterval', 'value' => 3]);
        $this->update('setting', ['value' => 1], ['name' => 'mailSummaryInterval', 'value' => 2]);
        $this->update('setting', ['value' => 2], ['name' => 'mailSummaryInterval', 'value' => 5]);
        
        $this->update('contentcontainer_setting', ['name' => 'mailSummaryInterval'], ['name' => 'receive_email_activities', 'module_id' => 'activity']);
        $this->update('contentcontainer_setting', ['value' => 5], ['name' => 'mailSummaryInterval', 'value' => 1]);
        $this->update('contentcontainer_setting', ['value' => 1], ['name' => 'mailSummaryInterval', 'value' => 3]);
        $this->update('contentcontainer_setting', ['value' => 1], ['name' => 'mailSummaryInterval', 'value' => 2]);
        $this->update('contentcontainer_setting', ['value' => 2], ['name' => 'mailSummaryInterval', 'value' => 5]);
        
        // This is now stored in settings
        $this->dropColumn('user', 'last_activity_email');
    }

    public function down()
    {
        echo "m170112_115052_settings cannot be reverted.\n";

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
