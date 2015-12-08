<?php

use yii\db\Schema;
use yii\db\Migration;

class m140303_125031_password extends Migration
{

    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        // Create New User Password Table
        $this->createTable('user_password', array(
            'id' => 'pk',
            'user_id' => 'int(10) DEFAULT NULL',
            'algorithm' => 'varchar(20) DEFAULT NULL',
            'password' => 'text DEFAULT NULL',
            'salt' => 'text DEFAULT NULL',
            'created_at' => 'datetime DEFAULT NULL',
                ), $tableOptions);

        $this->createIndex('idx_user_id', 'user_password', 'user_id', false);


        // Fix: Migrate Passwords from User Table to UserPasswords
        $algorithm = 'sha1md5';
        $rows = (new \yii\db\Query())
                ->select("*")
                ->from('user')
                ->all();
        foreach ($rows as $row) {
            $password = str_replace('___enc___', '', $row['password']);
            $this->update('user_password', ['user_id' => $row['id'], 'password' => $password, 'algorithm' => $algorithm, 'salt' => \humhub\models\Setting::Get('secret'), 'created_at' => new \yii\db\Expression('NOW()')]);
        }
        $this->dropColumn('user', 'password');
    }

    public function down()
    {
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
