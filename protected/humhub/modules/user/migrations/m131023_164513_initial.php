<?php


use yii\db\Migration;

class m131023_164513_initial extends Migration
{

    public function up()
    {

        $this->createTable('group', array(
            'id' => 'pk',
            'space_id' => 'int(10) DEFAULT NULL',
            'name' => 'varchar(45) DEFAULT NULL',
            'description' => 'text DEFAULT NULL',
            'created_at' => 'datetime DEFAULT NULL',
            'created_by' => 'int(11) DEFAULT NULL',
            'updated_at' => 'datetime DEFAULT NULL',
            'updated_by' => 'int(11) DEFAULT NULL',
                ), '');

        $this->createTable('group_admin', array(
            'id' => 'pk',
            'user_id' => 'int(11) NOT NULL',
            'group_id' => 'int(11) NOT NULL',
            'created_at' => 'datetime NOT NULL',
            'created_by' => 'int(11) NOT NULL',
            'updated_at' => 'datetime NOT NULL',
            'updated_by' => 'int(11) NOT NULL',
                ), '');


        $this->createTable('profile', array(
            'user_id' => 'int(11) NOT NULL',
                ), '');

        $this->addPrimaryKey('pk_profile', 'profile', 'user_id');

        $this->createTable('profile_field', array(
            'id' => 'pk',
            'profile_field_category_id' => 'int(11) NOT NULL',
            'module_id' => 'varchar(255) DEFAULT NULL',
            'field_type_class' => 'varchar(255) NOT NULL',
            'field_type_config' => 'text DEFAULT NULL',
            'internal_name' => 'varchar(100) NOT NULL',
            'title' => 'varchar(255) NOT NULL',
            'description' => 'text DEFAULT NULL',
            'sort_order' => 'int(11) NOT NULL',
            'required' => 'tinyint(4) DEFAULT NULL',
            'show_at_registration' => 'tinyint(4) DEFAULT NULL',
            'editable' => 'tinyint(4) NOT NULL DEFAULT \'1\'',
            'visible' => 'tinyint(4) NOT NULL',
            'created_at' => 'datetime DEFAULT NULL',
            'created_by' => 'int(11) DEFAULT NULL',
            'updated_at' => 'datetime DEFAULT NULL',
            'updated_by' => 'int(11) DEFAULT NULL',
                ), '');

        $this->createTable('profile_field_category', array(
            'id' => 'pk',
            'title' => 'varchar(255) NOT NULL',
            'description' => 'text NOT NULL',
            'sort_order' => 'int(11) NOT NULL',
            'module_id' => 'int(11) DEFAULT NULL',
            'visibility' => 'tinyint(4) NOT NULL',
            'created_at' => 'datetime DEFAULT NULL',
            'created_by' => 'int(11) DEFAULT NULL',
            'updated_at' => 'datetime DEFAULT NULL',
            'updated_by' => 'int(11) DEFAULT NULL',
                ), '');


        $this->createTable('user', array(
            'id' => 'pk',
            'guid' => 'varchar(45) DEFAULT NULL',
            'user_invite_id' => 'int(11) DEFAULT NULL',
            'wall_id' => 'int(11) DEFAULT NULL',
            'group_id' => 'int(11) DEFAULT NULL',
            'status' => 'tinyint(4) NOT NULL DEFAULT \'2\'',
            'super_admin' => 'tinyint(4) NOT NULL',
            'username' => 'varchar(25) DEFAULT NULL',
            'email' => 'varchar(100) DEFAULT NULL',
            'password' => 'varchar(200) DEFAULT NULL',
            'auth_mode' => 'varchar(10) NOT NULL',
            'tags' => 'text DEFAULT NULL',
            'language' => 'varchar(5) NOT NULL',
            'receive_email_notifications' => 'tinyint(4) NOT NULL',
            'receive_email_messaging' => 'tinyint(4) NOT NULL',
            'receive_email_activities' => 'tinyint(4) NOT NULL',
            'last_activity_email' => 'datetime NOT NULL',
            'created_at' => 'datetime DEFAULT NULL',
            'created_by' => 'int(11) DEFAULT NULL',
            'updated_at' => 'datetime DEFAULT NULL',
            'updated_by' => 'int(11) DEFAULT NULL',
                ), '');

        $this->createTable('user_content', array(
            'id' => 'pk',
            'user_id' => 'int(11) NOT NULL',
            'object_model' => 'varchar(100) NOT NULL',
            'object_id' => 'int(11) NOT NULL',
            'created_at' => 'datetime NOT NULL',
            'created_by' => 'int(11) NOT NULL',
            'updated_at' => 'datetime NOT NULL',
            'updated_by' => 'int(11) NOT NULL',
                ), '');

        $this->createTable('user_follow', array(
            'user_follower_id' => 'int(11) NOT NULL',
            'user_followed_id' => 'int(11) NOT NULL',
            'created_at' => 'datetime DEFAULT NULL',
            'created_by' => 'int(11) DEFAULT NULL',
            'updated_at' => 'datetime DEFAULT NULL',
            'updated_by' => 'int(11) DEFAULT NULL',
                ), '');

        $this->addPrimaryKey('pk_user_follow', 'user_follow', 'user_follower_id,user_followed_id');


        try {
            // May already created
            $this->createTable('user_http_session', array(
                'id' => 'char(32) NOT NULL',
                'expire' => 'int(11) DEFAULT NULL',
                'user_id' => 'int(11) DEFAULT NULL',
                'data' => 'longblob DEFAULT NULL',
                    ), '');
            $this->addPrimaryKey('pk_user_http_session', 'user_http_session', 'id');
        } catch (Exception $ex) {
            
        }



        $this->createTable('user_invite', array(
            'id' => 'pk',
            'user_originator_id' => 'int(11) DEFAULT NULL',
            'space_invite_id' => 'int(11) DEFAULT NULL',
            'email' => 'varchar(45) NOT NULL',
            'source' => 'varchar(45) DEFAULT NULL',
            'token' => 'varchar(45) DEFAULT NULL',
            'created_at' => 'datetime DEFAULT NULL',
            'created_by' => 'int(11) DEFAULT NULL',
            'updated_at' => 'datetime DEFAULT NULL',
            'updated_by' => 'int(11) DEFAULT NULL',
                ), '');


        $this->createTable('user_space_membership', array(
            'space_id' => 'int(11) NOT NULL',
            'user_id' => 'int(11) NOT NULL',
            'originator_user_id' => 'varchar(45) DEFAULT NULL',
            'status' => 'tinyint(4) DEFAULT NULL',
            'request_message' => 'text DEFAULT NULL',
            'last_visit' => 'datetime DEFAULT NULL',
            'invite_role' => 'tinyint(4) DEFAULT NULL',
            'admin_role' => 'tinyint(4) DEFAULT NULL',
            'share_role' => 'tinyint(4) DEFAULT NULL',
            'created_at' => 'datetime DEFAULT NULL',
            'created_by' => 'int(11) DEFAULT NULL',
            'updated_at' => 'datetime DEFAULT NULL',
            'updated_by' => 'int(11) DEFAULT NULL',
                ), '');


        $this->createTable('user_module', array(
            'id' => 'pk',
            'module_id' => 'varchar(255) NOT NULL',
            'user_id' => 'int(11) NOT NULL',
            'created_at' => 'datetime NOT NULL',
            'created_by' => 'int(11) NOT NULL',
            'updated_at' => 'datetime NOT NULL',
            'updated_by' => 'int(11) NOT NULL',
                ), '');

        $this->addPrimaryKey('pk_user_space_membership', 'user_space_membership', 'space_id,user_id');
    }

    public function down()
    {
        echo "m131023_164513_initial does not support migration down.\n";
        return false;

        $this->dropTable('user');
        $this->dropTable('user_content');
        $this->dropTable('user_follow');
        $this->dropTable('user_http_session');
        $this->dropTable('user_invite');
        $this->dropTable('user_module');
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
