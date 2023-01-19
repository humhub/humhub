<?php

use yii\db\Migration;

class m160227_073020_birthday_date extends Migration
{

    public function up()
    {
        $table = Yii::$app->db->schema->getTableSchema('profile');
        if (isset($table->columns['birthday'])) {
            $this->alterColumn('profile', 'birthday', \yii\db\Schema::TYPE_DATE);
        }
    }

    public function down()
    {
        echo "m160227_073020_birthday_date cannot be reverted.\n";

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
