<?php

use humhub\components\Migration;

class m150703_012735_typelength extends Migration
{
    public function up()
    {
        if ($this->safeRenameColumn('activity', 'type', 'class')) {
            $this->alterColumn('activity', 'class', 'varchar(100) NOT NULL');
        }
    }

    public function down()
    {
        echo "m150703_012735_typelength cannot be reverted.\n";

        return false;
    }
}
