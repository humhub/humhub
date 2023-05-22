<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models;

use humhub\components\ActiveRecord;
use humhub\libs\Helpers;
use humhub\modules\user\models\fieldtype\BaseType;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\Html;

/**
 * This is the model class for table "profile_field".
 *
 * @property integer $id
 * @property integer $profile_field_category_id
 * @property string $module_id
 * @property string $field_type_class
 * @property string $field_type_config
 * @property string $internal_name
 * @property string $title
 * @property string $description
 * @property integer $sort_order
 * @property integer $required
 * @property integer $show_at_registration
 * @property integer $editable
 * @property integer $visible
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 * @property string $ldap_attribute
 * @property string $translation_category
 * @property integer $is_system
 * @property integer $searchable
 * @property integer $directory_filter
 *
 * @property-read BaseType $fieldType
 */
class ProfileField extends ActiveRecord
{

    /**
     * Field Type Instance
     *
     * @var BaseType
     */
    private $_fieldType = null;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'profile_field';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            [['profile_field_category_id', 'field_type_class', 'internal_name', 'title', 'sort_order'], 'required'],
            [['profile_field_category_id', 'required', 'editable', 'searchable', 'show_at_registration', 'visible', 'sort_order', 'directory_filter'], 'integer'],
            [['module_id', 'field_type_class', 'title'], 'string', 'max' => 255],
            ['internal_name', 'string', 'max' => 100],
            [['ldap_attribute', 'translation_category'], 'string', 'max' => 255],
            ['internal_name', 'checkInternalName'],
            ['internal_name', 'match', 'not' => true, 'pattern' => '/[^a-zA-Z0-9_]/', 'message' => Yii::t('UserModule.profile', 'Only alphanumeric characters allowed!')],
            ['internal_name', 'match', 'pattern' => '/[a-zA-Z]/', 'message' => Yii::t('UserModule.profile', 'Must contain at least one character.')],
            ['field_type_class', 'checkType'],
            [['description'], 'safe'],
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(ProfileFieldCategory::class, ['id' => 'profile_field_category_id']);
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('UserModule.profile', 'ID'),
            'profile_field_category_id' => Yii::t('UserModule.profile', 'Profile Field Category'),
            'module_id' => Yii::t('UserModule.profile', 'Module'),
            'field_type_class' => Yii::t('UserModule.profile', 'Fieldtype'),
            'field_type_config' => Yii::t('UserModule.profile', 'Type Config'),
            'internal_name' => Yii::t('UserModule.profile', 'Internal Name'),
            'visible' => Yii::t('UserModule.profile', 'Visible'),
            'editable' => Yii::t('UserModule.profile', 'Editable'),
            'ldap_attribute' => Yii::t('UserModule.profile', 'LDAP Attribute'),
            'show_at_registration' => Yii::t('UserModule.profile', 'Show at registration'),
            'translation_category' => Yii::t('UserModule.profile', 'Translation Category ID'),
            'required' => Yii::t('UserModule.profile', 'Required'),
            'searchable' => Yii::t('UserModule.profile', 'Searchable'),
            'directory_filter' => Yii::t('UserModule.profile', 'Use as Directory filter'),
            'title' => Yii::t('UserModule.profile', 'Title'),
            'description' => Yii::t('UserModule.profile', 'Description'),
            'sort_order' => Yii::t('UserModule.profile', 'Sort order'),
            'created_at' => Yii::t('UserModule.profile', 'Created at'),
            'created_by' => Yii::t('UserModule.profile', 'Created by'),
            'updated_at' => Yii::t('UserModule.profile', 'Updated at'),
            'updated_by' => Yii::t('UserModule.profile', 'Updated by'),
        ];
    }

    /**
     * Before deleting a profile field, inform underlying ProfileFieldType for
     * cleanup.
     */
    public function beforeDelete()
    {
        if ($this->is_system) {
            return false;
        }

        $this->fieldType->delete();
        return parent::beforeDelete();
    }

    /**
     * After Save, also saving the underlying Field Type
     */
    public function afterSave($insert, $changedAttributes)
    {

        # Cause Endless
        #$this->fieldType->save();
        return parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Returns the ProfileFieldType Class for this Profile Field
     *
     * @return BaseType
     * @throws \yii\base\Exception
     */
    public function getFieldType(): ?BaseType
    {
        if ($this->_fieldType != null)
            return $this->_fieldType;

        if ($this->field_type_class != '' && Helpers::CheckClassType($this->field_type_class, fieldtype\BaseType::class)) {
            $type = $this->field_type_class;
            $this->_fieldType = new $type;
            $this->_fieldType->setProfileField($this);
            return $this->_fieldType;
        }

        return null;
    }

    /**
     * Returns The Form Definition to edit the ProfileField Model.
     *
     * @return array CForm Definition
     */
    public function getFormDefinition()
    {
        $categories = ProfileFieldCategory::find()->orderBy('sort_order')->all();
        $profileFieldTypes = new fieldtype\BaseType();
        $isVirtualField = (!$this->isNewRecord && $this->getFieldType()->isVirtual);
        $canBeDirectoryFilter = (!$this->isNewRecord && $this->getFieldType()->canBeDirectoryFilter);

        return [
            'ProfileField' => [
                'type' => 'form',
                #'showErrorSummary' => true,
                'elements' => [
                    'internal_name' => [
                        'type' => 'text',
                        'maxlength' => 32,
                        'class' => 'form-control',
                        'readonly' => !$this->isNewRecord, // Cannot be changed for existing record
                    ],
                    'title' => [
                        'type' => 'text',
                        'maxlength' => 32,
                        'class' => 'form-control',
                    ],
                    'description' => [
                        'type' => 'textarea',
                        'class' => 'form-control',
                    ],
                    'sort_order' => [
                        'type' => 'sortOrder',
                        'maxlength' => 32,
                        'class' => 'form-control',
                    ],
                    'translation_category' => [
                        'type' => 'text',
                        'maxlength' => 32,
                        'class' => 'form-control',
                        'value' => $this->getTranslationCategory(),
                    ],
                    //ToDo: Hide me, when Ldap Support is disabled
                    'ldap_attribute' => [
                        'type' => 'text',
                        'maxlength' => 255,
                        'class' => 'form-control',
                        'isVisible' => (!$isVirtualField)
                    ],
                    'required' => [
                        'type' => 'checkbox',
                        'isVisible' => (!$isVirtualField)
                    ],
                    'visible' => [
                        'type' => 'checkbox',
                    ],
                    'show_at_registration' => [
                        'type' => 'checkbox',
                        'isVisible' => (!$isVirtualField)
                    ],
                    'editable' => [
                        'type' => 'checkbox',
                        'isVisible' => (!$isVirtualField)
                    ],
                    'searchable' => [
                        'type' => 'checkbox',
                        'isVisible' => (!$isVirtualField)
                    ],
                    'directory_filter' => [
                        'type' => 'checkbox',
                        'isVisible' => ($canBeDirectoryFilter)
                    ],
                    'profile_field_category_id' => [
                        'type' => 'dropdownlist',
                        'items' => \yii\helpers\ArrayHelper::map($categories, 'id', 'title'),
                        'class' => 'form-control',
                    ],
                    'field_type_class' => [
                        'type' => 'dropdownlist',
                        'items' => $profileFieldTypes->getFieldTypes(),
                        'htmlOptions' => ['options' => $profileFieldTypes->getFieldTypeItemOptions()],
                        'class' => 'form-control',
                        'readonly' => !$this->isNewRecord, // Cannot be changed for existing record
                    ],
                ]
            ]];
    }

    /**
     * Validator which checks the given internal name.
     *
     * Also ensures that internal_name could not be changed on existing records.
     */
    public function checkInternalName()
    {

        // Little bit cleanup
        $this->internal_name = strtolower($this->internal_name);
        $this->internal_name = trim($this->internal_name);

        if (!$this->isNewRecord) {
            // Dont allow changes of internal_name - Maybe not the best way to check it.
            $currentProfileField = ProfileField::findOne(['id' => $this->id]);
            if ($this->internal_name != $currentProfileField->internal_name) {
                $this->addError('internal_name', Yii::t('UserModule.profile', 'Internal name could not be changed!'));
            }
        } else {
            // Check if Internal Name is not in use yet
            if (Profile::columnExists($this->internal_name)) {
                $this->addError('internal_name', Yii::t('UserModule.profile', 'Internal name already in use!'));
            }
        }
    }

    /**
     * Validator which checks the fieldtype
     *
     * Also ensures that field_type_class could not be changed on existing records.
     */
    public function checkType()
    {

        if (!$this->isNewRecord) {

            // Dont allow changes of internal_name - Maybe not the best way to check it.
            $currentProfileField = ProfileField::findOne(['id' => $this->id]);
            if ($this->field_type_class != $currentProfileField->field_type_class) {
                $this->addError('field_type_class', Yii::t('UserModule.profile', 'Field Type could not be changed!'));
            }
        } else {
            $profileFieldTypes = new fieldtype\BaseType();
            if (!key_exists($this->field_type_class, $profileFieldTypes->getFieldTypes())) {
                $this->addError('field_type_class', Yii::t('UserModule.profile', 'Invalid field type!'));
            }
        }
    }

    /**
     * Returns the users value for this profile field.
     *
     * @param User $user
     * @param bool $raw
     * @return string
     */
    public function getUserValue(User $user, $raw = true): ?string
    {
        return $this->fieldType->getUserValue($user, $raw);
    }

    /**
     * Returns the translation category
     * Defaults to: models_Profile
     *
     * @return string
     */
    public function getTranslationCategory()
    {

        if ($this->translation_category != "") {
            return $this->translation_category;
        }

        return "UserModule.profile";
    }

}
