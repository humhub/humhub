<?php


use yii\db\Migration;

class m151226_164234_authclient extends Migration
{

    public function up()
    {
        $this->createTable('user_auth', array(
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'source' => $this->string(255)->notNull(),
            'source_id' => $this->string(255)->notNull(),
        ));
        $this->addForeignKey('fk_user_id', 'user_auth', 'user_id', 'user', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        echo "m151226_164234_authclient cannot be reverted.\n";

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
