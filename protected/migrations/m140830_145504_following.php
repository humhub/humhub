<?php

class m140830_145504_following extends EDbMigration
{

    public function up()
    {

        $connection = $this->getDbConnection();

        $this->createTable('follow', array(
            'id' => 'pk',
            'object_model' => 'varchar(100) NOT NULL',
            'object_id' => 'int(11) NOT NULL',
            'user_id' => 'int(11) NOT NULL',
                ), '');

        $this->createIndex('index_user', 'follow', 'user_id', false);
        $this->createIndex('index_object', 'follow', 'object_model, object_id', false);

        // Migrate space_follow table to follow table
        $command = $connection->commandBuilder->createFindCommand('space_follow', new CDbCriteria);
        $reader = $command->query();
        foreach ($reader as $row) {
            $insertCommand = $connection->commandBuilder->createInsertCommand('follow', array(
                'user_id' => $row['user_id'],
                'object_model' => 'Space',
                'object_id' => $row['space_id'],
            ));
            $insertCommand->execute();
        }

        // Migrate user_follow table to follow table
        $command = $connection->commandBuilder->createFindCommand('user_follow', new CDbCriteria);
        $reader = $command->query();
        foreach ($reader as $row) {
            $insertCommand = $connection->commandBuilder->createInsertCommand('follow', array(
                'user_id' => $row['user_follower_id'],
                'object_model' => 'User',
                'object_id' => $row['user_followed_id'],
            ));
            $insertCommand->execute();
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
