<?php


use yii\db\Migration;

class m140830_145504_following extends Migration
{

    public function up()
    {


        $this->createTable('follow', array(
            'id' => 'pk',
            'object_model' => 'varchar(100) NOT NULL',
            'object_id' => 'int(11) NOT NULL',
            'user_id' => 'int(11) NOT NULL',
                ), '');

        $this->createIndex('index_user', 'follow', 'user_id', false);
        $this->createIndex('index_object', 'follow', 'object_model, object_id', false);

        // Fix: Migrate space_follow table to follow table
        $rows = (new \yii\db\Query())
                ->select("*")
                ->from('space_follow')
                ->all();
        foreach ($rows as $row) {
            $this->insert('follow', ['user_id' => $row['user_id'], 'object_model' => 'Space', 'object_id' => $row['space_id']]);
        }

        // Fix: Migrate user_follow table to follow table
        $rows = (new \yii\db\Query())
                ->select("*")
                ->from('user_follow')
                ->all();
        foreach ($rows as $row) {
            $this->insert('follow', ['user_id' => $row['user_follower_id'], 'object_model' => 'User', 'object_id' => $row['user_followed_id']]);
        }

        $this->dropTable('space_follow');
        $this->dropTable('user_follow');
    }

    public function down()
    {
        echo "m140830_145504_following does not support migration down.\n";
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
