<?php


use yii\db\Migration;

class m141015_173305_follow_notifications extends Migration
{

    public function up()
    {
        $this->renameTable('follow', 'user_follow');

        $this->addColumn('user_follow', 'send_notifications', 'boolean default "1"');


        // Fix: Migrate user_follow table to follow table
        $rows = (new \yii\db\Query())
                ->select("*")
                ->from('user_content')
                ->all();
        foreach ($rows as $row) {
            if ($row['object_model'] == 'Activity') {
                continue;
            }

            $this->insert('user_follow', ['user_id' => $row['user_id'], 'object_model' => $row['object_model'], 'object_id' => $row['object_id']]);
        }

        $this->dropTable('user_content');
    }

    public function down()
    {
        echo "m141015_173305_follow_notifications does not support migration down.\n";
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
