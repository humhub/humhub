<?php

/**
 * This is the model class for table "profile".
 *
 * The followings are the available columns in table 'profile':
 * @property integer $user_id
 *
 * @package humhub.modules_core.user.models
 * @since 0.5
 */
class Profile extends HActiveRecord
{

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Profile the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'profile';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {

        $rules = array();

        // On registration there is no user_id on validation
        if ($this->scenario != 'register') {
            $rules[] = array('user_id', 'required');
        }

        $rules[] = array('user_id', 'numerical', 'integerOnly' => true);

        foreach (ProfileField::model()->findAll() as $profileField) {
            if (!$profileField->visible && $this->scenario != 'adminEdit')
                continue;

            if (!$profileField->editable && $this->scenario != 'adminEdit' && $this->scenario != 'register')
                continue;

            if ($this->scenario == 'register' && !$profileField->show_at_registration)
                continue;

            $rules = array_merge($rules, $profileField->getFieldType()->getFieldRules());
        }

        return $rules;
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'user' => array(self::BELONGS_TO, 'User', 'user_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {

        /**
         * Hack for Yii Messages Command
         * 
         * Yii::t('UserModule.models_Profile', 'Firstname')
         * Yii::t('UserModule.models_Profile', 'Lastname')
         * Yii::t('UserModule.models_Profile', 'Title')
         * Yii::t('UserModule.models_Profile', 'Street')
         * Yii::t('UserModule.models_Profile', 'Zip')
         * Yii::t('UserModule.models_Profile', 'City')
         * Yii::t('UserModule.models_Profile', 'Country')
         * Yii::t('UserModule.models_Profile', 'State')
         * Yii::t('UserModule.models_Profile', 'About')
         * Yii::t('UserModule.models_Profile', 'Birthday')
         * Yii::t('UserModule.models_Profile', 'Hide year in profile')
         * 
         * Yii::t('UserModule.models_Profile', 'Gender')
         * Yii::t('UserModule.models_Profile', 'Male')
         * Yii::t('UserModule.models_Profile', 'Female')
         * Yii::t('UserModule.models_Profile', 'Custom')
         * Yii::t('UserModule.models_Profile', 'Hide year in profile')         * 
         * 
         * Yii::t('UserModule.models_Profile', 'Phone Private')
         * Yii::t('UserModule.models_Profile', 'Phone Work')
         * Yii::t('UserModule.models_Profile', 'Mobile')
         * Yii::t('UserModule.models_Profile', 'Fax')
         * Yii::t('UserModule.models_Profile', 'Skype Nickname')
         * Yii::t('UserModule.models_Profile', 'MSN')
         * Yii::t('UserModule.models_Profile', 'XMPP Jabber Address')
         * 
         * Yii::t('UserModule.models_Profile', 'Url')
         * Yii::t('UserModule.models_Profile', 'Facebook URL')
         * Yii::t('UserModule.models_Profile', 'LinkedIn URL')
         * Yii::t('UserModule.models_Profile', 'Xing URL')
         * Yii::t('UserModule.models_Profile', 'Youtube URL')
         * Yii::t('UserModule.models_Profile', 'Vimeo URL')
         * Yii::t('UserModule.models_Profile', 'Flickr URL')
         * Yii::t('UserModule.models_Profile', 'MySpace URL')
         * Yii::t('UserModule.models_Profile', 'Google+ URL')
         * Yii::t('UserModule.models_Profile', 'Twitter URL')
         */
        $labels = array();
        $labels['user_id'] = Yii::t('UserModule.models_Profile', 'User');

        foreach (ProfileField::model()->findAll() as $profileField) {
            $labels = array_merge($labels, $profileField->fieldType->getLabels());
        }

        return $labels;
    }

    /**
     * Returns the Profile as CForm
     */
    public function getFormDefinition()
    {

        $definition = array();
        $definition['elements'] = array();

        foreach (ProfileFieldCategory::model()->findAll(array('order' => 'sort_order')) as $profileFieldCategory) {

            $category = array(
                'type' => 'form',
                'title' => Yii::t($profileFieldCategory->getTranslationCategory(), $profileFieldCategory->title),
                'elements' => array(),
            );

            foreach (ProfileField::model()->findAllByAttributes(array('profile_field_category_id' => $profileFieldCategory->id), array('order' => 'sort_order')) as $profileField) {

                if (!$profileField->visible && $this->scenario != 'adminEdit')
                    continue;

                if ($this->scenario == 'register' && !$profileField->show_at_registration)
                    continue;

                // Mark field as editable when we are on register scenario and field should be shown at registration
                if ($this->scenario == 'register' && $profileField->show_at_registration)
                    $profileField->editable = true;
                
                // Mark field as editable when we are on adminEdit scenario
                if ($this->scenario == 'adminEdit') {
                    $profileField->editable = true;
                }
                // Dont allow editing of ldap syned fields - will be overwritten on next ldap sync.
                if ($this->user !== null && $this->user->auth_mode == User::AUTH_MODE_LDAP && $profileField->ldap_attribute != "") {
                    $profileField->editable = false;
                }

                $fieldDefinition = $profileField->fieldType->getFieldFormDefinition();
                $category['elements'] = array_merge($category['elements'], $fieldDefinition);
            }

            $definition['elements']['category_' . $profileFieldCategory->id] = $category;
        }

        return $definition;
    }

    public function beforeSave()
    {
        foreach ($this->attributes as $key => $value)
            if ($value == "")
                $this->$key = NULL;

        return parent::beforeSave();
    }

    /**
     * Checks if the given column name already exists on the profile table.
     *
     * @param String $name
     * @return Boolean
     */
    public function columnExists($name)
    {
        $table = Yii::app()->getDb()->getSchema()->getTable(Profile::model()->tableName());
        $columnNames = $table->getColumnNames();
        return (in_array($name, $columnNames));
    }

    /**
     * Returns all profile field categories with some user data
     * 
     * @todo Optimize me
     * @return Array ProfileFieldCategory
     */
    public function getProfileFieldCategories()
    {

        $categories = array();

        foreach (ProfileFieldCategory::model()->findAll(array('order' => 'sort_order')) as $category) {

            if (count($this->getProfileFields($category)) != 0) {
                $categories[] = $category;
            }
        }

        return $categories;
    }

    /**
     * Returns all profile fields with user data by given category
     * 
     * @todo Optimize me
     * @param ProfileFieldCategory $category
     * @return Array ProfileFields
     */
    public function getProfileFields(ProfileFieldCategory $category = null)
    {
        $fields = array();

        $criteria = new CDbCriteria();

        if ($category !== null) {
            $criteria->condition = "profile_field_category_id=:catId AND ";
            $criteria->params = array(':catId' => $category->id);
        }
        $criteria->condition .= "visible = 1";
        $criteria->order = "sort_order";

        foreach (ProfileField::model()->findAll($criteria) as $field) {

            if ($field->getUserValue($this->user) != "") {
                $fields[] = $field;
            }
        }

        return $fields;
    }

}
