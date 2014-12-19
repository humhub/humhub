<?php

/**
 * Deletes orphaned profile table entries
 */
class m141031_140056_cleanup_profiles extends EDbMigration
{

    public function up()
    {

        $connection = $this->getDbConnection();

        $criteria = new CDbCriteria();
        $criteria->join = 'LEFT JOIN user on profile.user_id = user.id';
        $criteria->condition = 'user.id IS NULL';

        $command = $connection->commandBuilder->createDeleteCommand('profile', $criteria);
        $command->execute();
    }

    public function down()
    {
        echo "m141031_140056_cleanup_profiles does not support migration down.\n";
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
