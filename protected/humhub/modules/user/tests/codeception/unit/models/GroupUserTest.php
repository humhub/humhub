<?php

namespace tests\codeception\unit\models;

use humhub\modules\user\models\Group;
use humhub\modules\user\models\GroupUser;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use yii\db\ActiveQuery;

class GroupUserTest extends HumHubDbTestCase
{
    public function testReturnTableName()
    {
        $this->assertEquals('group_user', GroupUser::tableName());
    }

    public function testReturnArrayOfRules()
    {
        $model = new GroupUser();
        $this->assertTrue(is_array($model->rules()));
    }

    public function testReturnScenarios()
    {
        $model = new GroupUser();
        $scenarios = $model->scenarios();
        $this->assertTrue(is_array($scenarios));
        $this->assertTrue(key_exists(GroupUser::SCENARIO_REGISTRATION, $scenarios));
        $this->assertEquals(['group_id'], $scenarios[GroupUser::SCENARIO_REGISTRATION]);
    }

    public function testReturnArrayOfAttributeLabels()
    {
        $model = new GroupUser();
        $this->assertTrue(is_array($model->attributeLabels()));
    }

    public function testReturnGroupRelationship()
    {
        $model = new GroupUser();
        $this->assertTrue($model->getGroup() instanceof ActiveQuery);
    }

    public function testReturnUserRelationship()
    {
        $model = new GroupUser();
        $this->assertTrue($model->getUser() instanceof ActiveQuery);
    }

    public function testValidateGroupId()
    {
        $model = new GroupUser();
        $model->scenario = GroupUser::SCENARIO_REGISTRATION;
        $model->load([
            'group_id' => 88
        ], '');

        $model->validate();
        $this->assertTrue($model->hasErrors());

        $model->load([
            'group_id' => Group::findOne(['name' => 'Users'])->id
        ], '');

        $this->assertTrue($model->validate());
    }

    public function testCreateNewUserGroup()
    {
        $model = new GroupUser();
        $model->load([
            'user_id' => User::findOne(['username' => 'User3'])->id,
            'group_id' => Group::findOne(['name' => 'Users'])->id
        ], '');
        $this->assertTrue($model->validate());
        $this->assertFalse($model->hasErrors());
        $this->assertTrue($model->save());
    }
}
