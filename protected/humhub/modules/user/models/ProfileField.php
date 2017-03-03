<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models;

use humhub\components\ActiveRecord;
use humhub\modules\user\models\fieldtype\BaseType;
use Yii;
use yii\db\ActiveQuery;

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
        return array(
            array(['profile_field_category_id', 'field_type_class', 'internal_name', 'title', 'sort_order'], 'required'),
            array(['profile_field_category_id', 'required', 'editable', 'searchable', 'show_at_registration', 'visible', 'sort_order'], 'integer'),
            array(['module_id', 'field_type_class', 'title'], 'string', 'max' => 255),
            array('internal_name', 'string', 'max' => 100),
            array(['ldap_attribute', 'translation_category'], 'string', 'max' => 255),
            array('internal_name', 'checkInternalName'),
            array('internal_name', 'match', 'not' => true, 'pattern' => '/[^a-zA-Z0-9_]/', 'message' => Yii::t('UserModule.models_ProfileField', 'Only alphanumeric characters allowed!')),
            array('field_type_class', 'checkType'),
            array(['description'], 'safe'),
        );
    }

    /**
     * @return ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(ProfileFieldCategory::className(), ['id' => 'profile_field_category_id']);
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => Yii::t('UserModule.models_ProfileField', 'ID'),
            'profile_field_category_id' => Yii::t('UserModule.models_ProfileField', 'Profile Field Category'),
            'module_id' => Yii::t('UserModule.models_ProfileField', 'Module'),
            'field_type_class' => Yii::t('UserModule.models_ProfileField', 'Fieldtype'),
            'field_type_config' => Yii::t('UserModule.models_ProfileField', 'Type Config'),
            'internal_name' => Yii::t('UserModule.models_ProfileField', 'Internal Name'),
            'visible' => Yii::t('UserModule.models_ProfileField', 'Visible'),
            'editable' => Yii::t('UserModule.models_ProfileField', 'Editable'),
            'ldap_attribute' => Yii::t('UserModule.models_ProfileField', 'LDAP Attribute'),
            'show_at_registration' => Yii::t('UserModule.models_ProfileField', 'Show at registration'),
            'translation_category' => Yii::t('UserModule.models_ProfileField', 'Translation Category ID'),
            'required' => Yii::t('UserModule.models_ProfileField', 'Required'),
            'searchable' => Yii::t('UserModule.models_ProfileField', 'Searchable'),
            'title' => Yii::t('UserModule.models_ProfileField', 'Title'),
            'description' => Yii::t('UserModule.models_ProfileField', 'Description'),
            'sort_order' => Yii::t('UserModule.models_ProfileField', 'Sort order'),
            'created_at' => Yii::t('UserModule.models_ProfileField', 'Created at'),
            'created_by' => Yii::t('UserModule.models_ProfileField', 'Created by'),
            'updated_at' => Yii::t('UserModule.models_ProfileField', 'Updated at'),
            'updated_by' => Yii::t('UserModule.models_ProfileField', 'Updated by'),
        );
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
     */
    public function getFieldType()
    {

        if ($this->_fieldType != null)
            return $this->_fieldType;

        if ($this->field_type_class != "" && \humhub\libs\Helpers::CheckClassType($this->field_type_class, fieldtype\BaseType::className())) {
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
     * @return Array CForm Definition
     */
    public function getFormDefinition()
    {
        $categories = ProfileFieldCategory::find()->orderBy('sort_order')->all();
        $profileFieldTypes = new fieldtype\BaseType();
        $definition = array(
            'ProfileField' => array(
                'type' => 'form',
                #'showErrorSummary' => true,
                'elements' => array(
                    'internal_name' => array(
                        'type' => 'text',
                        'maxlength' => 32,
                        'class' => 'form-control',
                    ),
                    'title' => array(
                        'type' => 'text',
                        'maxlength' => 32,
                        'class' => 'form-control',
                    ),
                    'description' => array(
                        'type' => 'textarea',
                        'class' => 'form-control',
                    ),
                    'sort_order' => array(
                        'type' => 'text',
                        'maxlength' => 32,
                        'class' => 'form-control',
                    ),
                    'translation_category' => array(
                        'type' => 'text',
                        'maxlength' => 32,
                        'class' => 'form-control',
                        'value' => $this->getTranslationCategory(),
                    ),
                    //ToDo: Hide me, when Ldap Support is disabled
                    'ldap_attribute' => array(
                        'type' => 'text',
                        'maxlength' => 255,
                        'class' => 'form-control',
                    ),
                    'required' => array(
                        'type' => 'checkbox',
                    ),
                    'visible' => array(
                        'type' => 'checkbox',
                    ),
                    'show_at_registration' => array(
                        'type' => 'checkbox',
                    ),
                    'editable' => array(
                        'type' => 'checkbox',
                    ),
                    'searchable' => array(
                        'type' => 'checkbox',
                    ),
                    'profile_field_category_id' => array(
                        'type' => 'dropdownlist',
                        'items' => \yii\helpers\ArrayHelper::map($categories, 'id', 'title'),
                        'class' => 'form-control',
                    ),
                    'field_type_class' => array(
                        'type' => 'dropdownlist',
                        'items' => $profileFieldTypes->getFieldTypes(),
                        'class' => 'form-control',
                    ),
                )
        ));

        // Field Type and Internal Name cannot be changed for existing records
        // So disable these fields.
        if (!$this->isNewRecord) {
            $definition['ProfileField']['elements']['field_type_class']['disabled'] = true;
            $definition['ProfileField']['elements']['internal_name']['readonly'] = true;
        }
        return $definition;
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
                $this->addError('internal_name', Yii::t('UserModule.models_ProfileField', 'Internal name could not be changed!'));
            }
        } else {
            // Check if Internal Name is not in use yet
            if (Profile::columnExists($this->internal_name)) {
                $this->addError('internal_name', Yii::t('UserModule.models_ProfileField', 'Internal name already in use!'));
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
                $this->addError('field_type_class', Yii::t('UserModule.models_ProfileField', 'Field Type could not be changed!'));
            }
        } else {
            $profileFieldTypes = new fieldtype\BaseType();
            if (!key_exists($this->field_type_class, $profileFieldTypes->getFieldTypes())) {
                $this->addError('field_type_class', Yii::t('UserModule.models_ProfileField', 'Invalid field type!'));
            }
        }
    }

    /**
     * Returns the users value for this profile field.
     *
     * @param type $user
     * @param type $raw
     *
     * @return type
     */
    public function getUserValue(User $user, $raw = true)
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

        return "UserModule.models_Profile";
    }

}
