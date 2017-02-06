<?php

use yii\db\Migration;

class m170123_125622_pinned extends Migration
{

    public function up()
    {
        $this->renameColumn('content', 'sticked', 'pinned');
    }

    public function down()
    {
        $this->renameColumn('content', 'pinned', 'sticked');
    }
}
