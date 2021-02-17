<?php

use yii\db\Migration;

/**
 * Class m210217_055359_protected_group
 */
class m210217_055359_protected_group extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('group', 'is_protected', $this->boolean()->notNull()->defaultValue(0)->after('is_default_group'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('group', 'is_protected');
    }
}
