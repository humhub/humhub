<?php

namespace tests\codeception\unit\models;

use humhub\modules\user\models\Profile;
use humhub\modules\user\models\ProfileField;
use humhub\modules\user\models\ProfileFieldCategory;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;

class ProfileModelTest extends HumHubDbTestCase
{
    public function testReturnTableName()
    {
        $this->assertEquals('profile', Profile::tableName());
    }

    public function testReturnBaseRules()
    {
        ProfileField::deleteAll();
        $model = new Profile();
        $this->assertEquals([
            [['user_id'], 'required'],
            [['user_id'], 'integer']
            ], $model->rules());
    }

    public function testReturnFullRules()
    {
        $model = new Profile();
        $rules = [
            [['user_id'], 'required'],
            [['user_id'], 'integer'],
            ['firstname', 'string', 'max' => 20],
            ['firstname', 'required'],
            ['lastname', 'string', 'max' => 30],
            ['lastname', 'required'],
            ['mobile', 'string', 'max' => 100],
            ['url', 'string', 'max' => 255],
        ];
        $this->assertEquals($rules, $model->rules());
    }

    public function testReturnScenarios()
    {
        $model = new Profile();
        $scenarios = $model->scenarios();
        $this->assertTrue(key_exists(Profile::SCENARIO_EDIT_ADMIN, $scenarios));
        $this->assertTrue(key_exists(Profile::SCENARIO_REGISTRATION, $scenarios));
        $this->assertTrue(key_exists(Profile::SCENARIO_EDIT_PROFILE, $scenarios));
    }

    public function testReturnArrayOfAttributeLabels()
    {
        $model = new Profile();
        $this->assertTrue(is_array($model->attributeLabels()));
    }

    public function testReturnArrayOfFormDefinition()
    {
        $model = new Profile();
        $this->assertTrue(is_array($model->getFormDefinition()));
    }

    public function testReturnArrayOfProfileFieldCategories()
    {
        $categories = ProfileFieldCategory::find()->all();

        $profile = User::findOne(['username' => 'Admin'])->profile;
        $profile->mobile = '11111111';
        $profile->url = 'http://test-url.com';
        $profile->save();
        $this->assertTrue(is_array($profile->getProfileFieldCategories()));

        $this->assertEquals(count($categories), count($profile->getProfileFieldCategories()));
    }
}
