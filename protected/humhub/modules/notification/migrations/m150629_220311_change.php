<?php

use humhub\components\Migration;

class m150629_220311_change extends Migration
{
    public function up()
    {
        $this->safeRenameColumn('notification', 'source_object_model', 'source_class');
        $this->safeRenameColumn('notification', 'source_object_id', 'source_pk');

        $this->safeRenameColumn('notification', 'target_object_id', 'obsolete_target_object_id');
        $this->safeRenameColumn('notification', 'target_object_model', 'obsolete_target_object_model');

        $this->safeAddColumn('notification', 'originator_user_id', 'int(11) DEFAULT NULL');
    }

    public function down()
    {
        echo "m150629_220311_change cannot be reverted.\n";

        return false;
    }
}
