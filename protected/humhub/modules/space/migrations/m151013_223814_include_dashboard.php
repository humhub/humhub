<?php

use yii\db\Schema;
use yii\db\Migration;

class m151013_223814_include_dashboard extends Migration
{
    public function up()
    {
        $this->addColumn('space_membership', 'show_at_dashboard', Schema::TYPE_BOOLEAN. ' DEFAULT 1');
    }

    public function down()
    {
        echo "m151013_223814_include_dashboard cannot be reverted.\n";

        return false;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
