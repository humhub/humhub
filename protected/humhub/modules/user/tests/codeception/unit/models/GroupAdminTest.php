<?php

namespace tests\codeception\unit\models;

use humhub\modules\user\models\GroupAdmin;
use tests\codeception\_support\HumHubDbTestCase;
use yii\db\ActiveQuery;

class GroupAdminTest extends HumHubDbTestCase
{
    public function testReturnTableName()
    {
        $this->assertEquals('group_admin', GroupAdmin::tableName());
    }

    public function testReturnArrayOfRules()
    {
        $model = new GroupAdmin();
        $this->assertTrue(is_array($model->rules()));
    }

    public function testReturnArrayOfAttributeLabels()
    {
        $model = new GroupAdmin();
        $this->assertTrue(is_array($model->attributeLabels()));
    }

    public function testReturnGroupRelationship()
    {
        $model = new GroupAdmin();
        $this->assertTrue($model->getGroup() instanceof ActiveQuery);
    }

    public function testReturnUserRelationship()
    {
        $model = new GroupAdmin();
        $this->assertTrue($model->getUser() instanceof ActiveQuery);
    }
}
