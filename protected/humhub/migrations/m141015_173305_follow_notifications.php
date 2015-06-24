<?php

class m141015_173305_follow_notifications extends EDbMigration
{

    public function up()
    {
        $this->renameTable('follow', 'user_follow');
        
        $this->addColumn('user_follow', 'send_notifications', 'boolean default "1"');

        $connection = $this->getDbConnection();
        
        // Migrate user_content table to follow table
        $command = $connection->commandBuilder->createFindCommand('user_content', new CDbCriteria);
        $reader = $command->query();
        foreach ($reader as $row) {
            if ($row['object_model'] == 'Activity') {
                continue;
            }
            
            $insertCommand = $connection->commandBuilder->createInsertCommand('user_follow', array(
                'user_id' => $row['user_id'],
                'object_model' => $row['object_model'],
                'object_id' => $row['object_id'],
            ));
            $insertCommand->execute();
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
