<?php

use yii\db\Migration;
use yii\db\Schema;

class m160217_161220_addCanLeaveFlag extends Migration
{
    public function up()
    {
        $this->addColumn('space_membership', 'can_cancel_membership', Schema::TYPE_INTEGER. ' DEFAULT 1');
        $this->addColumn('space', 'members_can_leave', Schema::TYPE_INTEGER. ' DEFAULT 1');
    }

    public function down()
    {
        echo "m160217_161220_addCanLeaveFlag cannot be reverted.\n";

        return false;
    }
}
