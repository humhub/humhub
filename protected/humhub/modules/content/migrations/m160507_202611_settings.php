<?php

use humhub\components\Migration;
use yii\db\Schema;
use yii\db\Query;

class m160507_202611_settings extends Migration
{

    public function up()
    {
        $this->createTable('contentcontainer_setting', [
            'id' => Schema::TYPE_PK,
            'module_id' => $this->string(50)->notNull(),
            'contentcontainer_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'name' => $this->string(50)->notNull(),
            'value' => Schema::TYPE_TEXT . ' NOT NULL',
        ]);
        $this->createIndex('settings-unique', 'contentcontainer_setting', ['module_id', 'contentcontainer_id', 'name'], true);
        $this->addForeignKey('fk-contentcontainerx', 'contentcontainer_setting', 'contentcontainer_id', 'contentcontainer', 'id', 'CASCADE', 'CASCADE');

        // Import old user settings
        $rows = (new Query())
                ->select("*, contentcontainer.id as cid")
                ->from('user_setting')
                ->leftJoin('contentcontainer', 'user_setting.user_id = contentcontainer.pk AND contentcontainer.class=:class', [':class' => \humhub\modules\user\models\User::className()])
                ->andWhere('contentcontainer.id IS NOT NULL')
                ->all();
        foreach ($rows as $row) {
            $this->insertSilent('contentcontainer_setting', [
                'module_id' => $row['module_id'],
                'contentcontainer_id' => $row['cid'],
                'name' => $row['name'],
                'value' => $row['value'],
            ]);
        }

        // Import old space settings
        $rows = (new Query())
                ->select("*, contentcontainer.id as cid")
                ->from('space_setting')
                ->leftJoin('contentcontainer', 'space_setting.space_id = contentcontainer.pk AND contentcontainer.class=:class', [':class' => humhub\modules\space\models\Space::className()])
                ->andWhere('contentcontainer.id IS NOT NULL')
                ->all();
        foreach ($rows as $row) {
            $this->insertSilent('contentcontainer_setting', [
                'module_id' => $row['module_id'],
                'contentcontainer_id' => $row['cid'],
                'name' => $row['name'],
                'value' => $row['value'],
            ]);
        }

        $this->dropTable('user_setting');
        $this->dropTable('space_setting');
    }

    public function down()
    {
        echo "m160507_202611_settings cannot be reverted.\n";

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
