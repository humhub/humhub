<?php

use yii\db\Schema;
use humhub\components\Migration;

class m150927_190830_create_contentcontainer extends Migration
{

    public function up()
    {
        $this->createTable('contentcontainer', array(
            'id' => Schema::TYPE_PK,
            'guid' => Schema::TYPE_STRING,
            'class' => Schema::TYPE_STRING,
            'pk' => Schema::TYPE_INTEGER,
            'owner_user_id' => Schema::TYPE_INTEGER,
            'wall_id' => Schema::TYPE_INTEGER,
                ), '');
        $this->createIndex('unique_target', 'contentcontainer', ['class', 'pk'], true);
        $this->createIndex('unique_guid', 'contentcontainer', ['guid'], true);

        $this->addColumn('space', 'contentcontainer_id', Schema::TYPE_INTEGER);
        $this->addColumn('user', 'contentcontainer_id', Schema::TYPE_INTEGER);
        
        $spaces = (new \yii\db\Query())->select("space.*")->from('space');
        foreach ($spaces->each() as $space) {
            $this->insertSilent('contentcontainer', [
                'guid' => $space['guid'],
                'class' => humhub\modules\space\models\Space::className(),
                'pk' => $space['id'],
                'owner_user_id' => $space['created_by'],
                'wall_id' => $space['wall_id'],
            ]);
            $this->updateSilent('space', ['contentcontainer_id' => Yii::$app->db->getLastInsertID()], 'space.id=:spaceId', [':spaceId' => $space['id']]);
        }        

        $users = (new \yii\db\Query())->select("user.*")->from('user');
        foreach ($users->each() as $user) {
            $this->insertSilent('contentcontainer', [
                'guid' => $user['guid'],
                'class' => \humhub\modules\user\models\User::className(),
                'pk' => $user['id'],
                'owner_user_id' => $user['id'],
                'wall_id' => $user['wall_id'],
            ]);
            $this->updateSilent('user', ['contentcontainer_id' => Yii::$app->db->getLastInsertID()], 'user.id=:userId', [':userId' => $user['id']]);
        }        
        
    }

    public function down()
    {
        echo "m150927_190830_create_contentcontainer cannot be reverted.\n";

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
