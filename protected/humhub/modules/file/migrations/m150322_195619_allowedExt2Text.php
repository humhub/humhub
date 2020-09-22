<?php


use yii\db\Migration;


class m150322_195619_allowedExt2Text extends Migration
{

    public function up()
    {
        $allowedExtensions = Yii::$app->getModule('file')->settings->get('allowedExtensions');
        if ($allowedExtensions != "") {
            Yii::$app->getModule('file')->settings->set('allowedExtensions', '');
            Yii::$app->getModule('file')->settings->set('allowedExtensions', $allowedExtensions);
        }

        $showFilesWidgetBlacklist = Yii::$app->getModule('file')->settings->get('showFilesWidgetBlacklist');
        if ($showFilesWidgetBlacklist != "") {
            Yii::$app->getModule('file')->settings->set('showFilesWidgetBlacklist', '');
            Yii::$app->getModule('file')->settings->set('showFilesWidgetBlacklist', $showFilesWidgetBlacklist);
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
