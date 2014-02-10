<?php

class m131213_165552_user_optimize extends EDbMigration {

    public function up() {

        $this->cleanupIndexes();

        $this->createIndex('unique_email', 'user', 'email', true);
        $this->createIndex('unique_username', 'user', 'username', true);
        $this->createIndex('unique_guid', 'user', 'guid', true);
        $this->createIndex('unique_wall_id', 'user', 'wall_id', true);

        $this->createIndex('unique_admin', 'group_admin', 'user_id,group_id', true);

        $this->createIndex('index_profile_field_category', 'profile_field', 'profile_field_category_id', false);

        $this->createIndex('index_user', 'user_content', 'user_id', false);
        $this->createIndex('index_object', 'user_content', 'object_model, object_id', false);

        $this->createIndex('index_status', 'user_space_membership', 'status', false);

        $this->createIndex('index_user_module', 'user_module', 'user_id, module_id', true);

        $this->createIndex('unique_token', 'user_invite', 'token', true);
        $this->createIndex('unique_email', 'user_invite', 'email', true);
    }

    public function down() {

        $this->cleanupIndexes();
        return true;
    }

    /**
     * Cleanup all indexes used before migrations
     * 
     */
    private function cleanupIndexes() {
        try {
            $this->dropIndex('unique_email', 'user');
        } catch (Exception $ex) {
            ;
        }
        try {
            $this->dropIndex('unique_username', 'user');
        } catch (Exception $ex) {
            ;
        }
        try {
            $this->dropIndex('unique_guid', 'user');
        } catch (Exception $ex) {
            ;
        }
        try {
            $this->dropIndex('unique_wall_id', 'user');
        } catch (Exception $ex) {
            ;
        }
        try {
            $this->dropIndex('idUser_UNIQUE', 'user');
        } catch (Exception $ex) {
            ;
        }
        try {
            $this->dropIndex('username_UNIQUE', 'user');
        } catch (Exception $ex) {
            ;
        }
        try {
            $this->dropIndex('idWall_UNIQUE', 'user');
        } catch (Exception $ex) {
            ;
        }
        try {
            $this->dropIndex('email_UNIQUE', 'user');
        } catch (Exception $ex) {
            ;
        }
        try {
            $this->dropIndex('idUserInvite_UNIQUE', 'user');
        } catch (Exception $ex) {
            ;
        }
        try {
            $this->dropIndex('fk_user_group1', 'user');
        } catch (Exception $ex) {
            ;
        }

        try {
            $this->dropIndex('user_id', 'user_content');
        } catch (Exception $ex) {
            ;
        }

        try {
            $this->dropIndex('index_object', 'user_content');
        } catch (Exception $ex) {
            ;
        }

        try {
            $this->dropIndex('index_user', 'user_content');
        } catch (Exception $ex) {
            ;
        }


        try {
            $this->dropIndex('index_profile_field_category', 'profile_field');
        } catch (Exception $ex) {
            ;
        }

        try {
            $this->dropIndex('unique_admin', 'group_admin');
        } catch (Exception $ex) {
            ;
        }
        try {
            $this->dropIndex('fk_UserFollows_User2', 'user_follow');
        } catch (Exception $ex) {
            ;
        }
        try {
            $this->dropIndex('fk_Group_has_User_User1', 'user_space_membership');
        } catch (Exception $ex) {
            ;
        }
        try {
            $this->dropIndex('status', 'user_space_membership');
        } catch (Exception $ex) {
            ;
        }
        try {
            $this->dropIndex('index_status', 'user_space_membership');
        } catch (Exception $ex) {
            ;
        }
        try {
            $this->dropIndex('index_user_module', 'user_module');
        } catch (Exception $ex) {
            ;
        }

        try {
            $this->dropIndex('email_UNIQUE', 'user_invite');
        } catch (Exception $ex) {
            ;
        }

        try {
            $this->dropIndex('token_UNIQUE', 'user_invite');
        } catch (Exception $ex) {
            ;
        }

        try {
            $this->dropIndex('fk_user_invite_Group1', 'user_invite');
        } catch (Exception $ex) {
            ;
        }

        try {
            $this->dropIndex('fk_user_invite_user1', 'user_invite');
        } catch (Exception $ex) {
            ;
        }
        try {
            $this->dropIndex('unique_token', 'user_invite');
        } catch (Exception $ex) {
            ;
        }

        try {
            $this->dropIndex('unqiue_email', 'user_invite');
        } catch (Exception $ex) {
            ;
        }
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
