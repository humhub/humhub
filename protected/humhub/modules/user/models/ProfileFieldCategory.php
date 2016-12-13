<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models;

use humhub\components\ActiveRecord;

/**
 * This is the model class for table "profile_field_category".
 *
 * @property integer $id
 * @property string $title
 * @property string $description
 * @property integer $sort_order
 * @property integer $module_id
 * @property integer $visibility
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 * @property string $translation_category
 * @property integer $is_system
 */
class ProfileFieldCategory extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'profile_field_category';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'sort_order'], 'required'],
            [['description'], 'string'],
            [['sort_order', 'module_id', 'visibility', 'is_system'], 'integer'],
            [['title', 'translation_category'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'description' => 'Description',
            'sort_order' => 'Sort Order',
            'module_id' => 'Module ID',
            'visibility' => 'Visibility',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'translation_category' => 'Translation Category',
            'is_system' => 'Is System',
        ];
    }

    public function beforeDelete()
    {
        if ($this->is_system) {
            return false;
        }

        return parent::beforeDelete();
    }

    /**
     * Returns the translation category
     * Defaults to: UserModule.profile
     *
     * @return string
     */
    public function getTranslationCategory()
    {

        if ($this->translation_category != "") {
            return $this->translation_category;
        }

        return "UserModule.models_ProfileFieldCategory";
    }

    public function getFields()
    {
        $query = $this->hasMany(ProfileField::className(), ['profile_field_category_id' => 'id']);
        $query->orderBy('sort_order');
        return $query;
    }

    /**
     * Internal
     * 
     * Just holds message labels for the Yii Message Command
     */
    private function translationOnly()
    {
        Yii::t('UserModule.models_ProfileFieldCategory', 'General');
        Yii::t('UserModule.models_ProfileFieldCategory', 'Communication');
        Yii::t('UserModule.models_ProfileFieldCategory', 'Social bookmarks');
    }

}
