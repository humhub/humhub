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

        $defaultUserGroupId = Yii::$app->getModule('user')->settings->get('auth.defaultUserGroup');

        // Convert option "None - shows dropdown in user registration." into separate enabled checkbox "Show Group Dropdown during registration":
        if (empty($defaultUserGroupId)) {
            Yii::$app->getModule('user')->settings->set('auth.showRegistrationUserGroup', '1');
        }

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
            $this->execute('INSERT INTO group_user (user_id, group_id, created_at, updated_at)
                SELECT user.id, ' . $group->id . ', NOW(), NOW()
                  FROM user
                  LEFT JOIN group_user ON group_user.user_id = user.id
                 WHERE group_user.id IS NULL
                   AND user.status = 1');
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
