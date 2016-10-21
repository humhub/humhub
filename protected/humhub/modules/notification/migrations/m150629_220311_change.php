<?php


use yii\db\Migration;

class m150629_220311_change extends Migration
{

    public function up()
    {
        $this->renameColumn('notification', 'source_object_model', 'source_class');
        $this->renameColumn('notification', 'source_object_id', 'source_pk');

        $this->renameColumn('notification', 'target_object_id', 'obsolete_target_object_id');
        $this->renameColumn('notification', 'target_object_model', 'obsolete_target_object_model');

        $this->addColumn('notification', 'originator_user_id', 'int(11) DEFAULT NULL');
    }

    public function down()
    {
        echo "m150629_220311_change cannot be reverted.\n";

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
