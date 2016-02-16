<?php

use yii\db\Migration;
use yii\db\Schema;

class m160216_161219_addCanLeaveFlagToMembership extends Migration
{
    public function up()
    {
        $this->addColumn('space_membership', 'can_leave', Schema::TYPE_BOOLEAN. ' DEFAULT 1');
    }

    public function down()
    {
        echo "m160216_161219_addCanLeaveFlagToMembership cannot be reverted.\n";

        return false;
    }
}
