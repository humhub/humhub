<?php

use humhub\components\Migration;
use humhub\modules\user\models\Group;

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
        $this->safeAddColumn(
            'group',
            'is_default_group',
            $this->boolean()->notNull()->defaultValue(0)->after('is_admin_group'),
        );

        $defaultUserGroupId = Yii::$app->getModule('user')->settings->get('auth.defaultUserGroup');

        // Convert option "None - shows dropdown in user registration." into separate enabled checkbox "Show Group Dropdown during registration":
        if (empty($defaultUserGroupId)) {
            Yii::$app->getModule('user')->settings->set('auth.showRegistrationUserGroup', '1');
        }

        // Try to create "Default Group" only for upgrade case because on new installation the group "Users" is used as default group:
        if (Yii::$app->isInstalled()) {
            // Move value from setting:auth.defaultUserGroup into new column group:is_default_group
            if (
                empty($defaultUserGroupId)
                || !($group = Group::findOne(['id' => $defaultUserGroupId]))
                || $group->is_admin_group
            ) {
                // Create one default Group if setting:auth.defaultUserGroup was not selected to any group:
                $group = new Group();
                $group->name = 'Default Group';
                $group->description = 'Group for users who are not assigned to any other group';
            }

            // Make default either old group that is used for new users or new created group above:
            $group->is_default_group = 1;
            if ($this->saveGroup($group)) {
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
        $this->safeDropColumn('group', 'is_default_group');
    }

    private function saveGroup(Group $group): bool
    {
        return $group->isNewRecord
            ? $this->insertGroup($group)
            : $this->updateGroup($group);
    }

    private function insertGroup(Group $group): bool
    {
        $this->insert('group', [
            'name' => $group->name,
            'description' => $group->description,
            'is_default_group' => $group->is_default_group,
            'created_at' => date('Y-m-d G:i:s'),
            'created_by' => 1,
            'updated_at' => date('Y-m-d G:i:s'),
            'updated_by' => 1,
        ]);

        if (!$this->db->lastInsertID) {
            return false;
        }

        $group->id = $this->db->lastInsertID;
        $group->setIsNewRecord(false);

        return true;
    }

    private function updateGroup(Group $group): bool
    {
        $this->update('group', [
            'is_default_group' => $group->is_default_group,
        ], ['id' => $group->id]);

        return true;
    }
}
