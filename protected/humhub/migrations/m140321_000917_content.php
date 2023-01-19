<?php


use yii\db\Migration;

class m140321_000917_content extends Migration
{

    public function up()
    {
        // Fix: empty user_id in content table
        $rows = (new \yii\db\Query())
                ->select("*")
                ->from('content')
                ->where(['IS', 'user_id', new \yii\db\Expression('NULL')])
                ->all();
        foreach ($rows as $row) {
            $this->update('content', ['user_id' => $row['created_by']], ['id' => $row['id']]);
        }

        $this->createIndex('index_object_model', 'content', 'object_model, object_id', true);
        $this->createIndex('index_guid', 'content', 'guid', true);
        $this->createIndex('index_space_id', 'content', 'space_id', false);
        $this->createIndex('index_user_id', 'content', 'user_id', false);
    }

    public function down()
    {
        echo "m140321_000917_content does not support migration down.\n";
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
