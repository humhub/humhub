<?php

use humhub\modules\user\models\Group;
use yii\db\Migration;

/**
 * Class m201228_064513_default_group
 */
class m201228_064513_default_group extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('group', 'is_default_group', $this->boolean()->notNull()->defaultValue(0)->after('is_admin_group'));

        // Create one default Group:
        $group = new Group();
        $group->name = 'Default Group';
        $group->description = 'Group for users who are not assigned to any other group';
        $group->is_default_group = 1;
        $group->save();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Delete default Group:
        $group = Group::find()
            ->where(['name' => 'Default Group'])
            ->andWhere(['is_default_group' => '1'])
            ->one();
        if ($group) {
            $group->delete();
        }

        $this->dropColumn('group', 'is_default_group');
    }
}
