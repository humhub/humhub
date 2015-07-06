<?php

namespace humhub\modules\user\models;

use Yii;

/**
 * This is the model class for table "profile".
 *
 * @property integer $user_id
 * @property string $firstname
 * @property string $lastname
 * @property string $title
 * @property string $gender
 * @property string $street
 * @property string $zip
 * @property string $city
 * @property string $country
 * @property string $state
 * @property integer $birthday_hide_year
 * @property string $birthday
 * @property string $about
 * @property string $phone_private
 * @property string $phone_work
 * @property string $mobile
 * @property string $fax
 * @property string $im_skype
 * @property string $im_msn
 * @property integer $im_icq
 * @property string $im_xmpp
 * @property string $url
 * @property string $url_facebook
 * @property string $url_linkedin
 * @property string $url_xing
 * @property string $url_youtube
 * @property string $url_vimeo
 * @property string $url_flickr
 * @property string $url_myspace
 * @property string $url_googleplus
 * @property string $url_twitter
 */
class Profile extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'profile';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [['user_id'], 'required'],
            [['user_id'], 'integer'],
        ];

        foreach (ProfileField::find()->all() as $profileField) {

            // Not visible fields: Admin Only
            if (!$profileField->visible && $this->scenario != 'editAdmin')
                continue;

            // Not Editable: only visibible on Admin Edit or Registration (if enabled)
            if (!$profileField->editable && $this->scenario != 'editAdmin' && $this->scenario != 'registration')
                continue;

            if ($this->scenario == 'registration' && !$profileField->show_at_registration)
                continue;

            $rules = array_merge($rules, $profileField->getFieldType()->getFieldRules());
        }
        return $rules;
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['editAdmin'] = array();
        $scenarios['registration'] = array();
        $scenarios['editProfile'] = array();

        $fields = array();
        foreach (ProfileField::find()->all() as $profileField) {
            $scenarios['editAdmin'][] = $profileField->internal_name;
            if ($profileField->editable) {
                $scenarios['editProfile'][] = $profileField->internal_name;
            }
            if ($profileField->show_at_registration) {
                $scenarios['registration'][] = $profileField->internal_name;
            }
        }
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'firstname' => 'Firstname',
            'lastname' => 'Lastname',
            'title' => 'Title',
            'gender' => 'Gender',
            'street' => 'Street',
            'zip' => 'Zip',
            'city' => 'City',
            'country' => 'Country',
            'state' => 'State',
            'birthday_hide_year' => 'Birthday Hide Year',
            'birthday' => 'Birthday',
            'about' => 'About',
            'phone_private' => 'Phone Private',
            'phone_work' => 'Phone Work',
            'mobile' => 'Mobile',
            'fax' => 'Fax',
            'im_skype' => 'Im Skype',
            'im_msn' => 'Im Msn',
            'im_icq' => 'Im Icq',
            'im_xmpp' => 'Im Xmpp',
            'url' => 'Url',
            'url_facebook' => 'Url Facebook',
            'url_linkedin' => 'Url Linkedin',
            'url_xing' => 'Url Xing',
            'url_youtube' => 'Url Youtube',
            'url_vimeo' => 'Url Vimeo',
            'url_flickr' => 'Url Flickr',
            'url_myspace' => 'Url Myspace',
            'url_googleplus' => 'Url Googleplus',
            'url_twitter' => 'Url Twitter',
        ];

        /*
          $labels = array();
          $labels['user_id'] = Yii::t('UserModule.models_Profile', 'User');

          foreach (ProfileField::model()->findAll() as $profileField) {
          $labels = array_merge($labels, $profileField->fieldType->getLabels());
          }

          return $labels;
         */
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * Returns the Profile as CForm
     */
    public function getFormDefinition()
    {

        $definition = array();
        $definition['elements'] = array();

        foreach (ProfileFieldCategory::find()->orderBy('sort_order')->all() as $profileFieldCategory) {

            $category = array(
                'type' => 'form',
                'title' => Yii::t($profileFieldCategory->getTranslationCategory(), $profileFieldCategory->title),
                'elements' => array(),
            );

            foreach (ProfileField::find()->orderBy('sort_order')->where(['profile_field_category_id' => $profileFieldCategory->id])->all() as $profileField) {

                if (!$profileField->visible && $this->scenario != 'editAdmin')
                    continue;

                if ($this->scenario == 'registration' && !$profileField->show_at_registration)
                    continue;

                // Mark field as editable when we are on register scenario and field should be shown at registration
                if ($this->scenario == 'registration' && $profileField->show_at_registration)
                    $profileField->editable = true;

                // Mark field as editable when we are on adminEdit scenario
                if ($this->scenario == 'editAdmin') {
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

    public function beforeSave($insert)
    {
        foreach (ProfileField::find()->all() as $profileField) {
            $key = $profileField->internal_name;
            $this->$key = $profileField->getFieldType()->beforeProfileSave($this->$key);
        }

        return parent::beforeSave($insert);
    }

    /**
     * Checks if the given column name already exists on the profile table.
     *
     * @param String $name
     * @return Boolean
     */
    public static function columnExists($name)
    {
        $table = Yii::$app->getDb()->getSchema()->getTableSchema(self::tableName(), true);
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

        foreach (ProfileFieldCategory::find()->orderBy('sort_order')->all() as $category) {

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

        $query = ProfileField::find();
        $query->where(['visible' => 1]);
        $query->orderBy('sort_order');
        if ($category !== null) {
            $query->andWhere(['profile_field_category_id' => $category->id]);
        }
        foreach ($query->all() as $field) {

            if ($field->getUserValue($this->user) != "") {
                $fields[] = $field;
            }
        }

        return $fields;
    }

}
