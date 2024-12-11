<?php

use humhub\components\Migration;

class m160408_100725_rename_groupadmin_to_manager extends Migration
{
    public function up()
    {
        $this->safeRenameColumn('group_user', 'is_group_admin', 'is_group_manager');
    }

    public function down()
    {
        echo "m160408_100725_rename_groupadmin_to_manager cannot be reverted.\n";

        return false;
    }
}
