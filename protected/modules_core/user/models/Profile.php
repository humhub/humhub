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
            if (!$profileField->visible && !$this->scenario == 'adminEdit')
                continue;

            if (!$profileField->editable && !$this->scenario == 'adminEdit')
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
         * Yii::t('UserModule.profile', 'Firstname')
         * Yii::t('UserModule.profile', 'Lastname')
         * Yii::t('UserModule.profile', 'Title')
         * Yii::t('UserModule.profile', 'Street')
         * Yii::t('UserModule.profile', 'Zip')
         * Yii::t('UserModule.profile', 'City')
         * Yii::t('UserModule.profile', 'Country')
         * Yii::t('UserModule.profile', 'State')
         * Yii::t('UserModule.profile', 'About')
         * 
         * Yii::t('UserModule.profile', 'Phone Private')
         * Yii::t('UserModule.profile', 'Phone Work')
         * Yii::t('UserModule.profile', 'Mobile')
         * Yii::t('UserModule.profile', 'Fax')
         * Yii::t('UserModule.profile', 'Skype Nickname')
         * Yii::t('UserModule.profile', 'MSN')
         * Yii::t('UserModule.profile', 'XMPP Jabber Address')
         * 
         * Yii::t('UserModule.profile', 'Url')
         * Yii::t('UserModule.profile', 'Facebook URL')
         * Yii::t('UserModule.profile', 'LinkedIn URL')
         * Yii::t('UserModule.profile', 'Xing URL')
         * Yii::t('UserModule.profile', 'Youtube URL')
         * Yii::t('UserModule.profile', 'Vimeo URL')
         * Yii::t('UserModule.profile', 'Flickr URL')
         * Yii::t('UserModule.profile', 'MySpace URL')
         * Yii::t('UserModule.profile', 'Google+ URL')
         * Yii::t('UserModule.profile', 'Twitter URL')
         */
        
        $labels = array();
        $labels['user_id'] = Yii::t('UserModule.base', 'User');

        foreach (ProfileField::model()->findAll() as $profileField) {
            $labels[$profileField->internal_name] = Yii::t($profileField->getTranslationCategory(),$profileField->title);
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
                'title' => $profileFieldCategory->title,
                'elements' => array(),
            );

            foreach (ProfileField::model()->findAllByAttributes(array('profile_field_category_id' => $profileFieldCategory->id), array('order' => 'sort_order')) as $profileField) {

                if (!$profileField->visible && $this->scenario != 'adminEdit')
                    continue;

                if ($this->scenario == 'register' && !$profileField->show_at_registration)
                    continue;

                // Mark field as editable when we are on adminEdit scenario
                if ($this->scenario == 'adminEdit') {
                    $profileField->editable = true;
                }
                
                $fieldDefinition = $profileField->fieldType->getFieldFormDefinition();
                $category['elements'] = array_merge($category['elements'], $fieldDefinition);
                
                
            }

            $definition['elements']['category_' . $profileFieldCategory->id] = $category;
        }

        return $definition;
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

}
