<?php

use humhub\models\Setting;
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

        $defaultUserGroupId = Yii::$app->getModule('user')->settings->get('auth.defaultUserGroup');

        // Convert option "None - shows dropdown in user registration." into separate enabled checkbox "Show Group Dropdown during registration":
        if (empty($defaultUserGroupId)) {
            Yii::$app->getModule('user')->settings->set('auth.showRegistrationUserGroup', '1');
        }

        // Try to create "Default Group" only for upgrade case because on new installation the group "Users" is used as default group:
        if (Setting::isInstalled()) {
            // Move value from setting:auth.defaultUserGroup into new column group:is_default_group
            if (empty($defaultUserGroupId) ||
                !($group = Group::findOne(['id' => $defaultUserGroupId])) ||
                $group->is_admin_group) {
                // Create one default Group if setting:auth.defaultUserGroup was not selected to any group:
                $group = new Group();
                $group->name = 'Default Group';
                $group->description = 'Group for users who are not assigned to any other group';
            }

            // Make default either old group that is used for new users or new created group above:
            $group->is_default_group = 1;
            if ($group->save()) {
                // Assign users to the Default Group who were not assigned to any other group before:
                $group->assignDefaultGroup();
            }
        }

        // Remove old setting:
        Yii::$app->getModule('user')->settings->delete('auth.defaultUserGroup');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('group', 'is_default_group');
    }
}
