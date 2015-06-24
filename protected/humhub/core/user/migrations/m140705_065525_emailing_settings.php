<?php

/**
 * Migrate: 
 *  receive_email_notifications 
 *  receive_email_messaging 
 *  receive_email_activities 
 * From user Table in user_setting Table.
 */
class m140705_065525_emailing_settings extends EDbMigration
{

    public function up()
    {

        $connection = $this->getDbConnection();

        $command = $connection->commandBuilder->createFindCommand('user', new CDbCriteria);
        $reader = $command->query();
        foreach ($reader as $row) {

            // Ignore deleted users
            if ($row['status'] == 3) {
                continue;
            }

            $userId = $row['id'];
            $receive_email_notifications = $row['receive_email_notifications'];
            $receive_email_messaging = $row['receive_email_messaging'];
            $receive_email_activities = $row['receive_email_activities'];

            $insertCommand = $connection->commandBuilder->createInsertCommand('user_setting', array(
                'user_id' => $userId,
                'module_id' => 'core',
                'name' => 'receive_email_notifications',
                'value' => $receive_email_notifications,
            ));
            $insertCommand->execute();

            $insertCommand = $connection->commandBuilder->createInsertCommand('user_setting', array(
                'user_id' => $userId,
                'module_id' => 'core',
                'name' => 'receive_email_messaging',
                'value' => $receive_email_messaging,
            ));
            $insertCommand->execute();

            $insertCommand = $connection->commandBuilder->createInsertCommand('user_setting', array(
                'user_id' => $userId,
                'module_id' => 'core',
                'name' => 'receive_email_activities',
                'value' => $receive_email_activities,
            ));
            $insertCommand->execute();
        }

        $this->dropColumn('user', 'receive_email_notifications');
        $this->dropColumn('user', 'receive_email_messaging');
        $this->dropColumn('user', 'receive_email_activities');

        if (HSetting::isInstalled()) {

            $this->insert('setting', array(
                'name' => 'receive_email_activities',
                'value' => '1',
                'name' => 'mailing'
            ));
            $this->insert('setting', array(
                'name' => 'receive_email_notifications',
                'value' => '2',
                'name' => 'mailing'
            ));
        }
    }

    public function down()
    {
        echo "m140705_065525_emailing_settings does not support migration down.\n";
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
