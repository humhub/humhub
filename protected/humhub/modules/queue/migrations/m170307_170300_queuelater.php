<?php

use yii\db\Migration;

class m170307_170300_queuelater extends Migration
{

    public function up()
    {
        $this->addColumn('queue', 'timeout', $this->integer()->defaultValue(0)->notNull()->after('created_at'));
    }

    public function down()
    {
        echo "m170307_170300_queuelater cannot be reverted.\n";
        return false;
    }

}
