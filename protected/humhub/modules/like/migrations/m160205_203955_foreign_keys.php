<?php

use yii\db\Schema;
use yii\db\Migration;

class m160205_203955_foreign_keys extends Migration
{

    public function up()
    {
        try {
            $this->addForeignKey('fk_like-created_by', 'like', 'created_by', 'user', 'id', 'CASCADE', 'CASCADE');
            $this->addForeignKey('fk_like-target_user_id', 'like', 'target_user_id', 'user', 'id', 'CASCADE', 'CASCADE');
        } catch (Exception $ex) {
            
        }
    }

    public function down()
    {
        $this->dropForeignKey('fk_like-created_by', 'like');
        $this->dropForeignKey('fk_like-target_user_id', 'like');

        return true;
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
