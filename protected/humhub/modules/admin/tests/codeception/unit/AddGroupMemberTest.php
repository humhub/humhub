<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\admin;

use humhub\modules\admin\models\forms\AddGroupMemberForm;
use humhub\modules\user\models\forms\EditGroupForm;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\web\HttpException;

/**
 * Ensures that adding group members is always bound to the group the members are added to,
 * so a manager of one group cannot write memberships of a group they do not manage.
 */
class AddGroupMemberTest extends HumHubDbTestCase
{
    /**
     * User2 is group manager of "Moderators" only and must not be able to add
     * anyone (including himself) to the unrelated "Users" group.
     */
    public function testGroupManagerCannotAddMembersToForeignGroup()
    {
        $user = $this->becomeUser('User2');
        $foreignGroup = Group::findOne(['name' => 'Users']);

        $this->assertFalse($foreignGroup->canManage());

        $form = new AddGroupMemberForm([
            'groupId' => $foreignGroup->id,
            'userGuids' => [$user->guid],
        ]);

        $this->assertTrue($form->validate());

        try {
            $form->save();
            $this->fail('Adding members to a group without manage permission should be denied.');
        } catch (HttpException $e) {
            $this->assertEquals(403, $e->statusCode);
        }

        $this->assertFalse($foreignGroup->isMember($user));
    }

    /**
     * The same request through the controller action must be denied as well.
     */
    public function testGroupManagerCannotAddMembersToForeignGroupViaController()
    {
        $user = $this->becomeUser('User2');
        $foreignGroup = Group::findOne(['name' => 'Users']);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        Yii::$app->request->enableCsrfValidation = false;
        Yii::$app->request->setBodyParams([
            'AddGroupMemberForm' => [
                'groupId' => $foreignGroup->id,
                'userGuids' => [$user->guid],
            ],
        ]);

        try {
            Yii::$app->runAction('admin/group/add-members');
            $this->fail('Adding members to a group without manage permission should be denied.');
        } catch (HttpException $e) {
            $this->assertEquals(403, $e->statusCode);
        }

        $this->assertFalse($foreignGroup->isMember($user));
    }

    /**
     * A group manager is still able to add members to a group they manage.
     */
    public function testGroupManagerCanAddMembersToOwnGroup()
    {
        $ownGroup = $this->createGroupManagedByUser2();
        $newMember = User::findOne(['username' => 'User3']);

        $this->becomeUser('User2');
        $this->assertTrue($ownGroup->canManage());

        $form = new AddGroupMemberForm([
            'groupId' => $ownGroup->id,
            'userGuids' => [$newMember->guid],
        ]);

        $this->assertTrue($form->validate());
        $this->assertTrue($form->save());
        $this->assertTrue($ownGroup->isMember($newMember));
    }

    /**
     * An administrator is still able to add members to any group.
     */
    public function testAdminCanAddMembersToAnyGroup()
    {
        $groupIds = [
            $this->createGroupManagedByUser2()->id,
            Group::getAdminGroupId(),
        ];

        $this->becomeUser('Admin');
        $newMember = User::findOne(['username' => 'User3']);

        foreach ($groupIds as $groupId) {
            $group = Group::findOne(['id' => $groupId]);

            $form = new AddGroupMemberForm([
                'groupId' => $group->id,
                'userGuids' => [$newMember->guid],
            ]);

            $this->assertTrue($form->validate());
            $this->assertTrue($form->save());
            $this->assertTrue($group->isMember($newMember));
        }
    }

    /**
     * Creates a group managed by User2 without default Spaces, so that adding a member
     * does not trigger any Space membership side effects.
     */
    private function createGroupManagedByUser2(): Group
    {
        $this->becomeUser('Admin');

        $group = new EditGroupForm([
            'name' => 'User2 Group',
            'managerGuids' => [User::findOne(['username' => 'User2'])->guid],
        ]);
        $this->assertTrue($group->save());

        return Group::findOne(['id' => $group->id]);
    }

    /**
     * The admin group stays protected for non administrators.
     */
    public function testGroupManagerCannotAddMembersToAdminGroup()
    {
        $user = $this->becomeUser('User2');
        $adminGroup = Group::getAdminGroup();

        $form = new AddGroupMemberForm([
            'groupId' => $adminGroup->id,
            'userGuids' => [$user->guid],
        ]);

        $this->assertTrue($form->validate());

        try {
            $form->save();
            $this->fail('Adding members to the administrator group should be denied.');
        } catch (HttpException $e) {
            $this->assertEquals(403, $e->statusCode);
        }

        $this->assertFalse($adminGroup->isMember($user));
    }
}
