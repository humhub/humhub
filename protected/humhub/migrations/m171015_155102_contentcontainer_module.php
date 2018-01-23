<?php

use yii\db\Migration;

class m171015_155102_contentcontainer_module extends Migration
{

    public function safeUp()
    {
        $this->createTable('contentcontainer_module', [
            'contentcontainer_id' => $this->integer()->notNull(),
            'module_id' => $this->char(100),
            'module_state' => $this->smallInteger(),
        ]);
        $this->addPrimaryKey('pk_contentcontainer_module', 'contentcontainer_module', ['contentcontainer_id', 'module_id']);
        $this->addForeignKey('fk_contentcontainer', 'contentcontainer_module', 'contentcontainer_id', 'contentcontainer', 'id', 'CASCADE', 'CASCADE');
        
        $sqlInsert = 'INSERT INTO contentcontainer_module (contentcontainer_id, module_id, module_state) ';
        $this->db->createCommand($sqlInsert . 'SELECT space.contentcontainer_id, module_id, state FROM space_module LEFT JOIN space ON space_module.space_id=space.id WHERE space.id IS NOT NULL')->execute();
        $this->db->createCommand($sqlInsert . 'SELECT user.contentcontainer_id, module_id, state FROM user_module LEFT JOIN user ON user_module.user_id=user.id WHERE user.id IS NOT NULL')->execute();

        $rows = (new \yii\db\Query())->select("*")->from('space_module')->where('space_id IS NULL OR space_id=0')->all();
        foreach ($rows as $row) {
            $reflect = new ReflectionClass(humhub\modules\space\models\Space::class);
            $module = Yii::$app->getModule($row['module_id']);
            $module->settings->set('moduleManager.defaultState.' . $reflect->getShortName(), $row['state']);
        }


        $rows = (new \yii\db\Query())->select("*")->from('user_module')->where('user_id IS NULL OR user_id=0')->all();
        foreach ($rows as $row) {
            $reflect = new ReflectionClass(\humhub\modules\user\models\User::class);
            $module = Yii::$app->getModule($row['module_id']);
            $module->settings->set('moduleManager.defaultState.' . $reflect->getShortName(), $row['state']);
        }
        
        $this->dropTable('user_module');
        $this->dropTable('space_module');
    }

    public function safeDown()
    {
        echo "m171015_155102_contentcontainer_module cannot be reverted.\n";

        return false;
    }

    /*
      // Use up()/down() to run migration code without a transaction.
      public function up()
      {

      }

      public function down()
      {
      echo "m171015_155102_contentcontainer_module cannot be reverted.\n";

      return false;
      }
     */
}
