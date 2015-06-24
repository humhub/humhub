<?php

class m150510_102900_update extends EDbMigration
{

    public function up()
    {
        if (HSetting::isInstalled()) {
            Yii::app()->search->rebuild();
        }
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
