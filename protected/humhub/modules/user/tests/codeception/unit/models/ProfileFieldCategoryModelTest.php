<?php

namespace tests\codeception\unit\models;

use humhub\modules\user\models\ProfileFieldCategory;
use tests\codeception\_support\HumHubDbTestCase;

class ProfileFieldCategoryModelTest extends HumHubDbTestCase
{
    public function testReturnTableName()
    {
        $this->assertEquals('profile_field_category', ProfileFieldCategory::tableName());
    }

    public function testReturnArrayOfRules()
    {
        $model = new ProfileFieldCategory();
        $this->assertTrue(is_array($model->rules()));
    }

    public function testReturnArrayOfAttributeLabels()
    {
        $model = new ProfileFieldCategory();
        $this->assertTrue(is_array($model->attributeLabels()));
    }

    public function testReturnTranslationCategory()
    {
        $model = ProfileFieldCategory::findOne(['title' => 'General']);
        $this->assertEquals('UserModule.profile', $model->getTranslationCategory());

        $model->load(['translation_category' => 'translation_category'], '');
        $this->assertEquals('translation_category', $model->getTranslationCategory());
    }

    public function testPreventToDeleteSystemFieldType()
    {
        $model = ProfileFieldCategory::findOne(['title' => 'General']);
        $this->assertFalse($model->delete());

        $validParams = [
           'title' => 'Hobbies',
            'sort_order' => 500
        ];
        $model = new ProfileFieldCategory();
        $model->load($validParams, '');
        $this->assertTrue($model->save());
        $this->assertEquals(1,  $model->delete());
    }
}
