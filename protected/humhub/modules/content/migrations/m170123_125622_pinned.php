<?php

use humhub\components\Migration;

class m170123_125622_pinned extends Migration
{
    public function up()
    {
        $this->safeRenameColumn('content', 'sticked', 'pinned');
    }

    public function down()
    {
        $this->safeRenameColumn('content', 'pinned', 'sticked');
    }
}
