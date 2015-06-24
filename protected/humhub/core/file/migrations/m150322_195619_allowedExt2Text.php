<?php

class m150322_195619_allowedExt2Text extends EDbMigration
{

    public function up()
    {
        $allowedExtensions = HSetting::Get('allowedExtensions', 'file');
        if ($allowedExtensions != "") {
            HSetting::Set('allowedExtensions', '', 'file');
            HSetting::SetText('allowedExtensions', $allowedExtensions, 'file');
        }

        $showFilesWidgetBlacklist = HSetting::Get('showFilesWidgetBlacklist', 'file');
        if ($showFilesWidgetBlacklist != "") {
            HSetting::Set('showFilesWidgetBlacklist', '', 'file');
            HSetting::SetText('showFilesWidgetBlacklist', $showFilesWidgetBlacklist, 'file');
        }
    }

    public function down()
    {
        echo "m150322_195619_allowedExt2Text does not support migration down.\n";
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
