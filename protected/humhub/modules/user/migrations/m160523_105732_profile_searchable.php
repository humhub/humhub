<?php

use yii\db\Migration;

class m160523_105732_profile_searchable extends Migration
{
    public function up()
    {
        $this->addColumn('profile_field', 'searchable', $this->boolean()->defaultValue(true));
    }

    public function down()
    {
        echo "m160523_105732_profile_searchable cannot be reverted.\n";

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
