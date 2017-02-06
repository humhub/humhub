<?php

use yii\db\Migration;
use yii\db\Expression;

class m160415_180332_wall_remove extends Migration
{

    public function up()
    {

        /**
         * Allow restart of this migration
         */
        try {
            $this->addColumn('content', 'show_in_stream', $this->boolean()->defaultValue(true));
            $this->addColumn('content', 'stream_sort_date', $this->dateTime());
        } catch (Exception $ex) {
            
        }

        /**
         * Populate stream_updated_at attribute
         */
        /*
        $this->db->createCommand('UPDATE content LEFT JOIN wall_entry ON wall_entry.content_id=content.id 
                SET content.stream_sort_date=wall_entry.updated_at
        WHERE content.stream_sort_date IS NULL')->excute();
        */
        
        $this->update('content', ['stream_sort_date' => new Expression('created_at')], ['IS', 'stream_sort_date', new Expression('NULL')]);

        /**
         * Populate show_in_stream attribute
         */
        /*
        $this->db->createCommand('UPDATE content LEFT JOIN wall_entry ON wall_entry.content_id=content.id 
                SET content.show_in_stream=0
        WHERE wall_entry.id IS NULL')->excute();
        */

        $this->dropForeignKey('fk_space-wall_id', 'space');
        $this->dropForeignKey('fk_wall_entry-wall_id', 'wall_entry');

        $this->dropColumn('user', 'wall_id');
        $this->dropColumn('space', 'wall_id');
        $this->dropColumn('contentcontainer', 'wall_id');

        $this->dropTable('wall_entry');
        $this->dropTable('wall');
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
