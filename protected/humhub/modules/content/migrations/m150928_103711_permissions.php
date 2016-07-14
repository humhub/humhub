<?php

use yii\db\Schema;
use yii\db\Migration;

class m150928_103711_permissions extends Migration
{

    public function up()
    {

        $this->createTable('contentcontainer_permission', array(
            'permission_id' =>  $this->string(150)->notNull(),
            'contentcontainer_id' => Schema::TYPE_INTEGER,
            'group_id' =>  $this->string(50)->notNull(),
            'module_id' => $this->string(50)->notNull(),
            'class' => Schema::TYPE_STRING,
            'state' => Schema::TYPE_BOOLEAN,
        ));
        $this->addPrimaryKey('contentcontainer_permission_pk', 'contentcontainer_permission', ['permission_id', 'group_id', 'module_id', 'contentcontainer_id']);
    }

    public function down()
    {
        echo "m150928_103711_permissions cannot be reverted.\n";

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
