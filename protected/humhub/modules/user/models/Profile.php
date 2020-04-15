<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models;

use humhub\modules\user\authclient\AuthClientHelpers;
use Yii;
use yii\db\ActiveRecord;

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
 * @property User $user
 */
class Profile extends ActiveRecord
{


    /**
     * @since 1.3.2
     */
    const SCENARIO_EDIT_ADMIN = 'editAdmin';
    const SCENARIO_REGISTRATION = 'registration';
    const SCENARIO_EDIT_PROFILE = 'editProfile';


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
            [['firstname', 'lastname'], 'trim'],
            [['user_id'], 'required'],
            [['user_id'], 'integer'],
        ];

        foreach (ProfileField::find()->all() as $profileField) {
            $rules = array_merge($rules, $profileField->getFieldType()->getFieldRules());
        }

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[static::SCENARIO_EDIT_ADMIN] = [];
        $scenarios[static::SCENARIO_REGISTRATION] = [];
        $scenarios[static::SCENARIO_EDIT_PROFILE] = [];

        // Get synced attributes if user is set
        $syncAttributes = [];
        if ($this->user !== null) {
            $syncAttributes = AuthClientHelpers::getSyncAttributesByUser($this->user);
        }

        foreach (ProfileField::find()->all() as $profileField) {
            // Some fields consist of multiple field definitions (e.g. Birthday)
            foreach ($profileField->fieldType->getFieldFormDefinition() as $fieldName => $definition) {

                // Skip automatically synced attributes (readonly)
                if (in_array($profileField->internal_name, $syncAttributes)) {
                    continue;
                }

                $scenarios[static::SCENARIO_EDIT_ADMIN][] = $fieldName;

                if ($profileField->editable && !in_array($profileField->internal_name, $syncAttributes)) {
                    $scenarios[static::SCENARIO_EDIT_PROFILE][] = $fieldName;
                }

                if ($profileField->show_at_registration) {
                    $scenarios[static::SCENARIO_REGISTRATION][] = $fieldName;
                }
            }
        }

        return $scenarios;
    }

    /**
     * Internal
     *
     * Just holds message labels for the Yii Message Command
     */
    private function translationOnly()
    {
        Yii::t('UserModule.profile', 'First name');
        Yii::t('UserModule.profile', 'Last name');
        Yii::t('UserModule.profile', 'Title');
        Yii::t('UserModule.profile', 'Street');
        Yii::t('UserModule.profile', 'Zip');
        Yii::t('UserModule.profile', 'City');
        Yii::t('UserModule.profile', 'Country');
        Yii::t('UserModule.profile', 'State');
        Yii::t('UserModule.profile', 'About');
        Yii::t('UserModule.profile', 'Birthday');
        Yii::t('UserModule.profile', 'Hide year in profile');

        Yii::t('UserModule.profile', 'Gender');
        Yii::t('UserModule.profile', 'Male');
        Yii::t('UserModule.profile', 'Female');
        Yii::t('UserModule.profile', 'Custom');
        Yii::t('UserModule.profile', 'Hide year in profile');

        Yii::t('UserModule.profile', 'Phone Private');
        Yii::t('UserModule.profile', 'Phone Work');
        Yii::t('UserModule.profile', 'Mobile');
        Yii::t('UserModule.profile', 'Fax');
        Yii::t('UserModule.profile', 'Skype Nickname');
        Yii::t('UserModule.profile', 'MSN');
        Yii::t('UserModule.profile', 'XMPP Jabber Address');

        Yii::t('UserModule.profile', 'Url');
        Yii::t('UserModule.profile', 'Facebook URL');
        Yii::t('UserModule.profile', 'LinkedIn URL');
        Yii::t('UserModule.profile', 'Xing URL');
        Yii::t('UserModule.profile', 'YouTube URL');
        Yii::t('UserModule.profile', 'Vimeo URL');
        Yii::t('UserModule.profile', 'Flickr URL');
        Yii::t('UserModule.profile', 'MySpace URL');
        Yii::t('UserModule.profile', 'Google+ URL');
        Yii::t('UserModule.profile', 'Twitter URL');
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = [];
        foreach (ProfileField::find()->all() as $profileField) {
            /** @var ProfileField $profileField */
            $labels = array_merge($labels, $profileField->getFieldType()->getLabels());
        }

        return $labels;
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Returns the Profile as CForm
     */
    public function getFormDefinition()
    {
        $definition = [];
        $definition['elements'] = [];

        $syncAttributes = [];
        if ($this->user !== null) {
            $syncAttributes = AuthClientHelpers::getSyncAttributesByUser($this->user);
        }

        $safeAttributes = $this->safeAttributes();

        foreach (ProfileFieldCategory::find()->orderBy('sort_order')->all() as $profileFieldCategory) {

            $category = [
                'type' => 'form',
                'title' => Yii::t($profileFieldCategory->getTranslationCategory(), $profileFieldCategory->title),
                'elements' => [],
            ];

            foreach (
                ProfileField::find()->orderBy('sort_order')
                    ->where(['profile_field_category_id' => $profileFieldCategory->id])->all() as $profileField
            ) {
                /** @var ProfileField $profileField */
                $profileField->editable = true;

                if (!in_array($profileField->internal_name, $safeAttributes)) {
                    if ($profileField->visible && $this->scenario != 'registration') {
                        $profileField->editable = false;
                    } else {
                        continue;
                    }
                }

                // Dont allow editing of ldap syned fields - will be overwritten on next ldap sync.
                if (in_array($profileField->internal_name, $syncAttributes)) {
                    $profileField->editable = false;
                }

                $fieldDefinition = $profileField->fieldType->getFieldFormDefinition();

                if(isset($fieldDefinition[$profileField->internal_name]) && !empty($profileField->description)) {
                    $fieldDefinition[$profileField->internal_name]['hint'] = $profileField->description;
                }

                $category['elements'] = array_merge($category['elements'], $fieldDefinition);

                $profileField->fieldType->loadDefaults($this);
            }

            $definition['elements']['category_' . $profileFieldCategory->id] = $category;
        }

        return $definition;
    }

    /**
     * @inheritdoc
     */
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
        Yii::$app->getDb()->getSchema()->refreshTableSchema(self::tableName());
        $table = Yii::$app->getDb()->getSchema()->getTableSchema(self::tableName(), true);
        $columnNames = $table->getColumnNames();

        return (in_array($name, $columnNames));
    }

    /**
     * Returns all profile field categories with some user data
     *
     * @return ProfileFieldCategory[]
     */
    public function getProfileFieldCategories()
    {
        $categories = [];

        foreach (ProfileFieldCategory::find()->orderBy('sort_order')->all() as $category) {
            if (count($this->getProfileFields($category)) > 0) {
                $categories[] = $category;
            }
        }

        return $categories;
    }

    /**
     * Returns all profile fields with user data by given category
     *
     * @param ProfileFieldCategory $category
     * @return ProfileField[]
     */
    public function getProfileFields(ProfileFieldCategory $category = null)
    {
        $fields = [];

        if ($this->user !== null) {
            $query = ProfileField::find()
                ->where(['visible' => 1])
                ->orderBy('sort_order');

            if ($category !== null) {
                $query->andWhere(['profile_field_category_id' => $category->id]);
            }

            /** @var ProfileField $profileFieldModels */
            $profileFieldModels = $query->all();

            foreach ($profileFieldModels as $field) {
                if (!empty($field->getUserValue($this->user))) {
                    $fields[] = $field;
                }
            }
        }

        return $fields;
    }

    /**
     * Soft delete will empty all profile fields except these defined in the module configuration.
     */
    public function softDelete()
    {
        $module = Yii::$app->getModule('user');
        /* @var $module \humhub\modules\user\Module */

        foreach (array_keys($this->getAttributes()) as $name) {
            if (!in_array($name, $module->softDeleteKeepProfileFields) && $name !== 'user_id') {
                $this->setAttribute($name, '');
            }
        }

        if (!$this->save(false)) {
            Yii::error('Could not soft delete profile!');
        }
    }

}
