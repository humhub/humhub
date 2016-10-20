<?php


use yii\db\Migration;

class m150510_102900_update extends Migration
{

    public function up()
    {
        #if (\humhub\models\Setting::isInstalled()) {
        #    \Yii::$app->search->rebuild();
        #}
    }

    public function down()
    {
        echo "m150510_102900_update does not support migration down.\n";
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
