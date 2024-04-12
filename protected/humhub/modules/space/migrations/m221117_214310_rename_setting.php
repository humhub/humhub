<?php

use yii\db\Migration;

/**
 * Class m221117_214310_rename_setting
 */
class m221117_214310_rename_setting extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('contentcontainer_setting', [
            'name' => 'hideMembers',
        ], ['name' => 'hideMembersSidebar', 'module_id' => 'space']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m221117_214310_rename_setting cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221117_214310_rename_setting cannot be reverted.\n";

        return false;
    }
    */
}
