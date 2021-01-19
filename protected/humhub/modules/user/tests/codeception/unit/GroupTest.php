<?php

namespace tests\codeception\unit;

use tests\codeception\_support\HumHubDbTestCase;
use humhub\modules\user\models\Group;
use Yii;

class GroupTest extends HumHubDbTestCase
{
    public function testRegistrationGroupsEnabled()
    {
        Yii::$app->getModule('user')->settings->set('auth.showRegistrationUserGroup', 1);

        $groups = Group::getRegistrationGroups();
        $this->assertCount(1, $groups);
        $this->assertEquals('Users', $groups[0]->name);

        $adminGroup = Group::getAdminGroup();
        $this->assertEquals(1, $adminGroup->is_admin_group);
        $adminGroup->show_at_registration = 1;
        $this->assertFalse($adminGroup->save());

        $groups = Group::getRegistrationGroups();
        $this->assertCount(1, $groups);
        $adminGroup->save(false);

        $groups = Group::getRegistrationGroups();
        $this->assertCount(1, $groups);

        // Update moderator group
        Group::findOne(['id' => 3])->updateAttributes(['show_at_registration' => 1]);

        // Make sure the admin group is not contained in registration groups even if show_at_registration is set
        $groups = Group::getRegistrationGroups();
        $this->assertCount(2, $groups);

        $this->assertEquals('Moderators', $groups[0]->name);
        $this->assertEquals('Users', $groups[1]->name);


    }

    public function testRegistrationGroupsDisabled()
    {
        Yii::$app->getModule('user')->settings->set('auth.showRegistrationUserGroup', 1);
        Group::findOne(['id' => 2])->updateAttributes(['show_at_registration' => 1]);
        Group::findOne(['id' => 3])->updateAttributes(['show_at_registration' => 1]);
        $this->assertCount(2, Group::getRegistrationGroups());

        Yii::$app->getModule('user')->settings->set('auth.showRegistrationUserGroup', 0);

        $groups = Group::getRegistrationGroups();
        $this->assertCount(1, $groups);
        $this->assertEquals(2, $groups[0]->id);
    }

}
