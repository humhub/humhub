<?php

use yii\db\Migration;

class m160714_142827_remove_space_id extends Migration
{

    public function up()
    {
        try {
            $this->dropColumn('comment', 'space_id');
        } catch (Exception $ex) {
            
        }
    }

    public function down()
    {
        echo "m160714_142827_remove_space_id cannot be reverted.\n";

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
