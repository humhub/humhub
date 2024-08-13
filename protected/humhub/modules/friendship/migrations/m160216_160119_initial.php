<?php


use yii\db\Migration;

class m160216_160119_initial extends Migration
{

    public function up()
    {
        $this->createTable('user_friendship', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'friend_user_id' => $this->integer()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ]);

        $this->createIndex('idx-friends', 'user_friendship', ['user_id', 'friend_user_id'], true);
        $this->addForeignKey('fk-user', 'user_friendship', 'user_id', 'user', 'id', 'CASCADE');
        $this->addForeignKey('fk-friend', 'user_friendship', 'friend_user_id', 'user', 'id', 'CASCADE');
    }

    public function down()
    {
        echo "m160216_160119_inital cannot be reverted.\n";

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
