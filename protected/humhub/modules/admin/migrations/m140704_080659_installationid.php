<?php


use yii\db\Migration;

class m140704_080659_installationid extends Migration
{

    public function up()
    {
        if (\humhub\models\Setting::isInstalled()) {

            $this->insert('setting', array(
                'name' => 'installationId',
                'value' => md5(uniqid("", true)),
                'module_id' => 'admin'
            ));
        }
    }

    public function down()
    {
        echo "m140704_080659_installationid does not support migration down.\n";
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
