<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\models\forms;

use humhub\modules\user\models\ProfileField;
use humhub\modules\user\models\ProfileFieldCategory;
use Yii;
use humhub\libs\DynamicConfig;
use yii\base\Model;

/**
 * PeopleSettingsForm
 * @since 1.9
 */
class PeopleSettingsForm extends Model
{

    public $detail1;
    public $detail2;
    public $detail3;
    public $defaultSorting;

    /**
     * @var array Cached options for card details from tables of user profile and its categories
     */
    private $detailOptions;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Set default values
        $this->detail1 = Yii::$app->settings->get('people.detail1', '');
        $this->detail2 = Yii::$app->settings->get('people.detail2', '');
        $this->detail3 = Yii::$app->settings->get('people.detail3', '');
        $this->defaultSorting = Yii::$app->settings->get('people.defaultSorting', 'lastlogin');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['detail1', 'in', 'range' => $this->getDetailKeys()],
            ['detail2', 'in', 'range' => $this->getDetailKeys()],
            ['detail3', 'in', 'range' => $this->getDetailKeys()],
            ['defaultSorting', 'in', 'range' => array_keys(self::getSortingOptions())],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'detail1' => Yii::t('AdminModule.user', 'Information 1'),
            'detail2' => Yii::t('AdminModule.user', 'Information 2'),
            'detail3' => Yii::t('AdminModule.user', 'Information 3'),
            'defaultSorting' => Yii::t('AdminModule.user', 'Default Sorting'),
        ];
    }

    /**
     * Saves the form
     * @return boolean
     */
    public function save()
    {
        Yii::$app->settings->set('people.detail1', $this->detail1);
        Yii::$app->settings->set('people.detail2', $this->detail2);
        Yii::$app->settings->set('people.detail3', $this->detail3);
        Yii::$app->settings->set('people.defaultSorting', $this->defaultSorting);

        return true;
    }

    public function getDetailOptions(): array
    {
        if (isset($this->detailOptions)) {
            return $this->detailOptions;
        }

        $this->detailOptions = ['' => Yii::t('AdminModule.user', 'None')];

        $profileFields = ProfileField::find()
            ->leftJoin('profile_field_category', 'profile_field_category.id = profile_field_category_id')
            ->orderBy('profile_field_category.sort_order, profile_field.sort_order')
            ->all();
        foreach ($profileFields as $profileField) {
            /* @var $profileField ProfileField */
            /* @var $profileFieldCategory ProfileFieldCategory */
            $profileFieldCategory = $profileField->getCategory()->one();

            if (!isset($this->detailOptions[$profileFieldCategory->title])) {
                $this->detailOptions[$profileFieldCategory->title] = [];
            }

            $this->detailOptions[$profileFieldCategory->title][$profileField->internal_name] = $profileField->title . ($profileField->visible ? '' : ' (' . Yii::t('AdminModule.user', 'Not visible') . ')');
        }

        return $this->detailOptions;
    }

    private function getDetailKeys(): array
    {
        $keys = [];
        $options = self::getDetailOptions();

        foreach ($options as $key => $option) {
            if (is_array($option)) {
                $keys = array_merge($keys, array_keys($option));
            } else {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    public static function getSortingOptions(): array
    {
        return [
            'firstname' => Yii::t('AdminModule.user', 'First name'),
            'lastname' => Yii::t('AdminModule.user', 'Last name'),
            'lastlogin' => Yii::t('AdminModule.user', 'Last login'),
        ];
    }

}
