<?php

use yii\db\Migration;

class m170118_162332_streamchannel extends Migration
{

    public function up()
    {
        $this->addColumn('content', 'stream_channel', $this->char(15)->null());
        $this->update('content', ['stream_channel' => 'activity', 'show_in_stream' => 0], ['object_model' => \humhub\modules\activity\models\Activity::className(), 'show_in_stream' => 1]);
        $this->update('content', ['stream_channel' => 'default'], ['show_in_stream' => 1]);
        $this->dropColumn('content', 'show_in_stream');
        $this->createIndex('stream_channe', 'content', 'stream_channel', false);
    }

    public function down()
    {
        echo "m170118_162332_streamchannel cannot be reverted.\n";

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
