<?php

use yii\db\Migration;

/**
 * Class m211124_180441_admin_group_label
 */
class m211124_180441_admin_group_label extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        Yii::t('AdminModule.base', 'Administrators');
        Yii::t('AdminModule.base', 'Default group for administrators of this HumHub Installation');
        Yii::t('AdminModule.base', 'Users');
        Yii::t('AdminModule.base', 'Default group for all newly registered users of the network');

        $this->update(
            'group',
            ['name' => 'Administrators', 'description' => 'Default group for administrators of this HumHub Installation'],
            ['is_admin_group' => 1, 'name' => 'Administrator']
        );

        $this->update(
            'group',
            ['description' => 'Default group for all newly registered users of the network'],
            ['description' => 'Example Group by Installer']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m211124_180441_admin_group_label cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m211124_180441_admin_group_label cannot be reverted.\n";

        return false;
    }
    */
}
