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

    public $userDetails;
    public $backsideLine1;
    public $backsideLine2;
    public $backsideLine3;
    public $defaultSorting;

    /**
     * @var array Cached options for backside lines from tables of user profile and its categories
     */
    private $backsideLineOptions;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Set default values
        $this->userDetails = Yii::$app->settings->get('people.userDetails', 'full');
        $this->backsideLine1 = Yii::$app->settings->get('people.backsideLine1', 'city');
        $this->backsideLine2 = Yii::$app->settings->get('people.backsideLine2', 'mobile');
        $this->backsideLine3 = Yii::$app->settings->get('people.backsideLine3', 'email_virtual');
        $this->defaultSorting = Yii::$app->settings->get('people.defaultSorting', 'lastlogin');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['userDetails', 'in', 'range' => array_keys($this->getUserDetailsOptions())],
            ['backsideLine1', 'in', 'range' => $this->getBacksideLineKeys()],
            ['backsideLine2', 'in', 'range' => $this->getBacksideLineKeys()],
            ['backsideLine3', 'in', 'range' => $this->getBacksideLineKeys()],
            ['defaultSorting', 'in', 'range' => array_keys(self::getSortingOptions())],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'userDetails' => Yii::t('AdminModule.user', 'User Details'),
            'backsideLine1' => Yii::t('AdminModule.user', 'Backside Details Line 1'),
            'backsideLine2' => Yii::t('AdminModule.user', 'Backside Details Line 2'),
            'backsideLine3' => Yii::t('AdminModule.user', 'Backside Details Line 3'),
            'defaultSorting' => Yii::t('AdminModule.user', 'Default Sorting'),
        ];
    }

    /**
     * Saves the form
     * @return boolean
     */
    public function save()
    {
        Yii::$app->settings->set('people.userDetails', $this->userDetails);
        Yii::$app->settings->set('people.backsideLine1', $this->backsideLine1);
        Yii::$app->settings->set('people.backsideLine2', $this->backsideLine2);
        Yii::$app->settings->set('people.backsideLine3', $this->backsideLine3);
        Yii::$app->settings->set('people.defaultSorting', $this->defaultSorting);

        DynamicConfig::rewrite();

        return true;
    }

    public function getUserDetailsOptions(): array
    {
        return [
            'full' => Yii::t('AdminModule.user', 'Show full user data'),
            'front' => Yii::t('AdminModule.user', 'Front only'),
            'back' => Yii::t('AdminModule.user', 'Back side only'),
        ];
    }

    public function getBacksideLineOptions(): array
    {
        if (isset($this->backsideLineOptions)) {
            return $this->backsideLineOptions;
        }

        $this->backsideLineOptions = ['' => Yii::t('AdminModule.user', 'None')];

        $profileFields = ProfileField::find()
            ->leftJoin('profile_field_category', 'profile_field_category.id = profile_field_category_id')
            ->orderBy('profile_field_category.sort_order, profile_field.sort_order')
            ->all();
        foreach ($profileFields as $profileField) {
            /* @var $profileField ProfileField */
            /* @var $profileFieldCategory ProfileFieldCategory */
            $profileFieldCategory = $profileField->getCategory()->one();

            if (!isset($this->backsideLineOptions[$profileFieldCategory->title])) {
                $this->backsideLineOptions[$profileFieldCategory->title] = [];
            }

            $this->backsideLineOptions[$profileFieldCategory->title][$profileField->internal_name] = $profileField->title . ($profileField->visible ? '' : ' (' . Yii::t('AdminModule.user', 'Not visible') . ')');
        }

        return $this->backsideLineOptions;
    }

    private function getBacksideLineKeys(): array
    {
        $keys = [];
        $options = self::getBacksideLineOptions();

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
