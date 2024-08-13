<?php

namespace tests\codeception\unit\models;

use humhub\modules\user\models\fieldtype\Number;
use humhub\modules\user\models\fieldtype\Text;
use humhub\modules\user\models\ProfileField;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use yii\db\ActiveQuery;

class ProfileFieldModelTest extends HumHubDbTestCase
{
    public function testReturnTableName()
    {
        $this->assertEquals('profile_field', ProfileField::tableName());
    }

    public function testReturnArrayOfRules()
    {
        $model = new ProfileField();
        $this->assertTrue(is_array($model->rules()));
    }

    public function testReturnCategoryRelationship()
    {
        $model = new ProfileField();
        $this->assertTrue($model->getCategory() instanceof ActiveQuery);
    }

    public function testReturnArrayOfAttributeLabels()
    {
        $model = new ProfileField();
        $this->assertTrue(is_array($model->attributeLabels()));
    }

    public function testReturnArrayOfFormDefinition()
    {
        $model = new ProfileField();
        $this->assertTrue(is_array($model->getFormDefinition()));
    }

    public function testReturnFieldType()
    {
        $validParams = [
            'profile_field_category_id' => 1,
            'field_type_class' => Text::class,
            'internal_name' => 'uniquename',
            'title' => 'Unique Title',
            'sort_order' => 900,
        ];
        $model = new ProfileField();

        $model->load(array_merge($validParams, ['field_type_class' => '']), '');
        $this->assertNull($model->getFieldType());

        $model->load($validParams, '');
        $fieldType = new Text;
        $fieldType->setProfileField($model);
        $this->assertEquals($fieldType, $model->getFieldType());
    }

    public function testCheckInternalNameForNewRecord()
    {
        $validParams = [
            'profile_field_category_id' => 1,
            'field_type_class' => Text::class,
            'internal_name' => 'uniquename',
            'title' => 'Unique Title',
            'sort_order' => 900
        ];
        $model = new ProfileField();
        $notValidParams = array_merge($validParams, ['internal_name' => 'firstname']);
        $model->load($notValidParams, '');
        $model->validate();
        $this->assertTrue($model->hasErrors());

        $notValidParams = array_merge($validParams, ['internal_name' => 'firstName']);
        $model->load($notValidParams, '');
        $model->validate();
        $this->assertTrue($model->hasErrors());

        $model->load($validParams, '');
        $model->validate();
        $this->assertFalse($model->hasErrors());
    }

    public function testCheckInternalNameForExistingRecord()
    {
        $validParams = [
            'profile_field_category_id' => 1,
            'field_type_class' => Text::class,
            'internal_name' => 'firstname',
            'title' => 'Unique Title',
            'sort_order' => 900,
        ];

        $model = ProfileField::findOne(['internal_name' => 'firstname']);
        $model->load(array_merge($validParams, ['internal_name' => 'uniquename']), '');
        $model->validate();
        $this->assertTrue($model->hasErrors());

        $definition = $model->getFormDefinition();
        $this->assertTrue($definition['ProfileField']['elements']['internal_name']['readonly']);

        $model->load($validParams, '');
        $model->validate();
        $this->assertFalse($model->hasErrors());
    }

    public function testCheckTypeForNewRecord()
    {
        $validParams = [
            'profile_field_category_id' => 1,
            'field_type_class' => Text::class,
            'internal_name' => 'uniquename',
            'title' => 'Unique Title',
            'sort_order' => 900
        ];
        $model = new ProfileField();
        $model->load(array_merge($validParams, ['field_type_class' => 'UnknownClassName']), '');
        $model->validate();
        $this->assertTrue($model->hasErrors());

        $model->load($validParams, '');
        $model->validate();
        $this->assertFalse($model->hasErrors());
    }

    public function testCheckTypeForExistingRecord()
    {
        $validParams = [
            'profile_field_category_id' => 1,
            'field_type_class' => Text::class,
            'internal_name' => 'firstname',
            'title' => 'Unique Title',
            'sort_order' => 900
        ];

        $model = ProfileField::findOne(['internal_name' => 'firstname']);
        $model->load(array_merge($validParams, ['field_type_class' => Number::class]), '');
        $model->validate();
        $this->assertTrue($model->hasErrors());

        $definition = $model->getFormDefinition();
        $this->assertTrue($definition['ProfileField']['elements']['field_type_class']['readonly']);

        $model->load($validParams, '');
        $model->validate();
        $this->assertFalse($model->hasErrors());
    }

    public function testReturnUserValue()
    {
        $model = ProfileField::findOne(['internal_name' => 'firstname']);
        $user = User::findOne(['username' => 'Admin']);
        $this->assertEquals('Admin', $model->getUserValue($user));
    }

    public function testReturnTranslationCategory()
    {
        $model = ProfileField::findOne(['internal_name' => 'firstname']);
        $this->assertEquals('UserModule.profile', $model->getTranslationCategory());

        $model->load(['translation_category' => 'Category'], '');
        $this->assertEquals('Category', $model->getTranslationCategory());
    }

    public function testPreventToDeleteSystemFieldType()
    {
        $model = ProfileField::findOne(['internal_name' => 'firstname']);
        $this->assertFalse($model->delete());

        $validParams = [
            'profile_field_category_id' => 1,
            'field_type_class' => Text::class,
            'internal_name' => 'uniquename',
            'title' => 'Unique Title',
            'sort_order' => 900
        ];
        $model = new ProfileField();
        $model->load($validParams, '');
        $this->assertTrue($model->save());
        $this->assertEquals(1,  $model->delete());
    }
}
