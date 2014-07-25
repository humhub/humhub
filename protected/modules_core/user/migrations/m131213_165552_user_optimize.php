<?php

class m131213_165552_user_optimize extends EDbMigration
{

    public function up()
    {

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

    public function down()
    {

        return true;
    }

}
