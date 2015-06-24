<?php

class m140321_000917_content extends EDbMigration {

    public function up() {
    
        $connection = $this->getDbConnection();        
        
        $criteria = new CDbCriteria();
        $criteria->condition = 'user_id IS NULL';
        
        $command = $connection->commandBuilder->createFindCommand('content', $criteria);
        $reader = $command->query();
        
        foreach ($reader as $row) {
            $updateCriteria = new CDbCriteria();
            $updateCriteria->condition = 'id='.$row['id'];
            $updateCommand = $connection->commandBuilder->createUpdateCommand('content', array('user_id' => $row['created_by']), $updateCriteria);
            $updateCommand->execute();
        }
        
        $this->createIndex('index_object_model', 'content', 'object_model, object_id', true);
        $this->createIndex('index_guid', 'content', 'guid', true);
        $this->createIndex('index_space_id', 'content', 'space_id', false);
        $this->createIndex('index_user_id', 'content', 'user_id', false);

    }

    public function down() {
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
