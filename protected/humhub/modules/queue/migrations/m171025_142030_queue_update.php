<?php

use yii\db\Migration;

class m171025_142030_queue_update extends Migration
{

    public $tableName = '{{%queue}}';

    public function safeUp()
    {
        $this->dropTable($this->tableName);
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'channel' => $this->string()->notNull(),
            'job' => $this->binary()->notNull(),
            'pushed_at' => $this->integer()->notNull(),
            'ttr' => $this->integer()->notNull(),
            'delay' => $this->integer()->notNull(),
            'reserved_at' => $this->integer(),
            'attempt' => $this->integer(),
            'done_at' => $this->integer(),
        ]);
        $this->createIndex('channel', $this->tableName, 'channel');
        $this->createIndex('reserved_at', $this->tableName, 'reserved_at');

        $this->addColumn($this->tableName, 'priority', $this->integer()->unsigned()->notNull()->defaultValue(1024)->after('delay'));
        $this->createIndex('priority', $this->tableName, 'priority');
    }

    public function safeDown()
    {
        echo "m171025_142030_queue_update cannot be reverted.\n";

        return false;
    }

    /*
      // Use up()/down() to run migration code without a transaction.
      public function up()
      {

      }

      public function down()
      {
      echo "m171025_142030_queue_update cannot be reverted.\n";

      return false;
      }
     */
}
