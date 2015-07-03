<?php

use yii\db\Schema;
use yii\db\Migration;
use humhub\models\Setting;

class m150322_195619_allowedExt2Text extends Migration
{

    public function up()
    {
        $allowedExtensions = Setting::Get('allowedExtensions', 'file');
        if ($allowedExtensions != "") {
            Setting::Set('allowedExtensions', '', 'file');
            Setting::SetText('allowedExtensions', $allowedExtensions, 'file');
        }

        $showFilesWidgetBlacklist = Setting::Get('showFilesWidgetBlacklist', 'file');
        if ($showFilesWidgetBlacklist != "") {
            Setting::Set('showFilesWidgetBlacklist', '', 'file');
            Setting::SetText('showFilesWidgetBlacklist', $showFilesWidgetBlacklist, 'file');
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
