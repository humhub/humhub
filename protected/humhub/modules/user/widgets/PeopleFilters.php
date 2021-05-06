<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\components\Widget;
use humhub\libs\Html;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\ProfileField;
use Yii;

/**
 * PeopleFilters displays the filters on the directory people page
 *
 * @since 1.9
 * @author Luke
 */
class PeopleFilters extends Widget
{

    /**
     * @inheritdoc
     */
    public function run()
    {
        $groupOptions = [];
        $groups = Group::findAll(['show_at_directory' => 1]);
        if ($groups) {
            $groupOptions[''] = Yii::t('UserModule.base', 'Any');
            foreach ($groups as $group) {
                $groupOptions[$group->id] = $group->name;
            }
        }

        $profileFields = ProfileField::findAll(['directory_filter' => 1]);

        return $this->render('peopleFilters', [
            'groupOptions' => $groupOptions,
            'profileFields' => $profileFields,
        ]);
    }

    public static function getDefaultValue(string $filter): string
    {
        switch ($filter) {
            case 'sort':
                return PeopleCard::config('defaultSorting');
        }

        return '';
    }

    public static function getValue(string $filter)
    {
        $defaultValue = self::getDefaultValue($filter);

        if (preg_match('/^(.+?)\[(.+?)\]$/', $filter, $arrayMatch)) {
            $array = Yii::$app->request->get($arrayMatch[1]);
            return isset($array[$arrayMatch[2]]) ? $array[$arrayMatch[2]] : $defaultValue;
        }

        return Yii::$app->request->get($filter, $defaultValue);
    }

    public static function renderProfileFieldFilter(ProfileField $profileField): string
    {
        $profileFieldType = $profileField->getFieldType();

        if (!$profileFieldType) {
            return '';
        }

        $definition = $profileFieldType->getFieldFormDefinition();
        $fieldType = isset($definition[$profileField->internal_name]['type']) ? $definition[$profileField->internal_name]['type'] : null;

        $filterName = 'fields[' . $profileField->internal_name . ']';
        $filterOptions = ['class' => 'form-control form-search-filter'];

        switch ($fieldType) {
            case 'text':
                return Html::textInput($filterName, PeopleFilters::getValue($filterName), $filterOptions);
            case 'dropdownlist':
                $filterOptions['data-action-change'] = 'people.applyFilters';
                $selectItems = array_merge(['' => Yii::t('UserModule.base', 'Any')], $definition[$profileField->internal_name]['items']);
                return Html::dropDownList($filterName, PeopleFilters::getValue($filterName), $selectItems, $filterOptions);
        }

        return '';
    }

}
