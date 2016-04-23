<?php

use yii\db\Migration;
use yii\db\Expression;

class m160415_180332_wall_remove extends Migration
{

    public function up()
    {
        $this->addColumn('content', 'show_in_stream', $this->boolean()->defaultValue(true));
        $this->addColumn('content', 'stream_sort_date', $this->dateTime());

        /**
         * Populate stream_updated_at attribute
         */
        $this->update('content', [
            'stream_sort_date' => new Expression('(SELECT updated_at FROM wall_entry WHERE wall_entry.content_id=content.id LIMIT 1)')
                ], [
            'IS', 'stream_sort_date', new Expression('NULL')
        ]);
        $this->update('content', ['stream_sort_date' => new Expression('created_at')], ['IS', 'stream_sort_date', new Expression('NULL')]);

        /**
         * Populate show_in_stream attribute
         */
        $this->update('content', [
            'show_in_stream' => 0
                ], [
            'IS', new Expression('(SELECT wall_entry.id FROM wall_entry WHERE wall_entry.content_id=content.id LIMIT 1)'), new Expression('NULL')
        ]);

        $this->dropTable('wall');
        $this->dropTable('wall_entry');
        $this->dropColumn('user', 'wall_id');
        $this->dropColumn('space', 'wall_id');
        $this->dropColumn('contentcontainer', 'wall_id');
    }

    public function down()
    {
        echo "m160415_180332_wall_remove cannot be reverted.\n";

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
