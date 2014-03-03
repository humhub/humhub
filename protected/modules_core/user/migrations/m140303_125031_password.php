<?php

class m140303_125031_password extends EDbMigration {

    public function up() {

        $connection = $this->getDbConnection();

        // Create New User Password Table
        $this->createTable('user_password', array(
            'id' => 'pk',
            'user_id' => 'int(10) DEFAULT NULL',
            'algorithm' => 'varchar(20) DEFAULT NULL',
            'password' => 'text DEFAULT NULL',
            'salt' => 'text DEFAULT NULL',
            'created_at' => 'datetime DEFAULT NULL',
                ), '');

        $this->createIndex('idx_user_id', 'user_password', 'user_id', false);

        // Migrate Passwords from User Table to UserPasswords
        $command = $connection->commandBuilder->createFindCommand('user', new CDbCriteria);
        $reader = $command->query();
        $algorithm = 'sha1md5';
        foreach ($reader as $row) {
            $userId = $row['id'];
            $password = $row['password'];
            $password = str_replace('___enc___', '', $password);
            $insertCommand = $connection->commandBuilder->createInsertCommand('user_password', array(
                'user_id' => $userId,
                'password' => $password,
                'algorithm' => $algorithm,
                'salt' => HSetting::Get('secret'),
                'created_at' => new CDbExpression("NOW()")
            ));
            $insertCommand->execute();
        }

        $this->dropColumn('user', 'password');
    }

    public function down() {
        echo "m140303_125031_password does not support migration down.\n";
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
