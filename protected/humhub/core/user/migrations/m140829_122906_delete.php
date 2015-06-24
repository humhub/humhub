<?php

class m140829_122906_delete extends EDbMigration
{

    public function up()
    {
        // Really delete deleted users
        $connection = $this->getDbConnection();

        $criteria = new CDbCriteria();
        $criteria->condition = 'status=3';
        $command = $connection->commandBuilder->createDeleteCommand('user', $criteria);
        $command->execute();

        $this->dropColumn('user', 'user_invite_id');

        # Remove status default value
        $this->alterColumn('user', 'status', 'tinyint(4)');
    }

    public function down()
    {
        echo "m140829_122906_delete does not support migration down.\n";
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
