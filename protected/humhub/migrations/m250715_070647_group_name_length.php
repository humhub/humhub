<?php

use yii\db\Migration;

class m250715_070647_group_name_length extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('group', 'name', $this->string(120));
    }

    public function safeDown()
    {
        $this->alterColumn('group', 'name', $this->string(45));
    }
}
