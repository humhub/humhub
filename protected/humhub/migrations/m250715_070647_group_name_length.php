<?php

use humhub\components\Migration;

class m250715_070647_group_name_length extends Migration
{
    public function safeUp()
    {
        $this->safeAlterColumn('group', 'name', $this->string(120));
    }

    public function safeDown()
    {
        $this->safeAlterColumn('group', 'name', $this->string(45));
    }
}
