<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\libs\Html;
use humhub\modules\admin\models\forms\PeopleSettingsForm;
use humhub\modules\ui\widgets\DirectoryFilters;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\ProfileField;
use Yii;

/**
 * PeopleFilters displays the filters on the directory people page
 *
 * @since 1.9
 * @author Luke
 */
class PeopleFilters extends DirectoryFilters
{
    /**
     * @inheritdoc
     */
    public $pageUrl = '/user/people';

    protected function initDefaultFilters()
    {
        // Keyword
        $this->addFilter('keyword', [
            'title' => Yii::t('UserModule.base', 'Find people by their profile data or user tags'),
            'placeholder' => Yii::t('UserModule.base', 'Search...'),
            'type' => 'text',
            'wrapperClass' => 'col-md-6 form-search-filter-keyword',
            'afterInput' => Html::submitButton('<span class="fa fa-search"></span>', ['class' => 'form-button-search']),
            'sortOrder' => 100,
        ]);

        // Group
        $groupOptions = [];
        $groups = Group::findAll(['show_at_directory' => 1]);
        if ($groups) {
            $groupOptions[''] = Yii::t('UserModule.base', 'Any');
            foreach ($groups as $group) {
                $groupOptions[$group->id] = $group->name;
            }

            $this->addFilter('groupId', [
                'title' => Yii::t('UserModule.base', 'User Group'),
                'type' => 'dropdown',
                'options' => $groupOptions,
                'sortOrder' => 200,
            ]);
        }

        // Sorting
        $this->addFilter('sort', [
            'title' => Yii::t('SpaceModule.base', 'Sorting'),
            'type' => 'dropdown',
            'options' => PeopleSettingsForm::getSortingOptions(true),
            'sortOrder' => 300,
        ]);

        // Connection
        $connectionOptions = [
            '' => Yii::t('UserModule.base', 'All'),
            'followers' => Yii::t('UserModule.base', 'Followers'),
            'following' => Yii::t('UserModule.base', 'Following'),
        ];
        if (Yii::$app->getModule('friendship')->settings->get('enable')) {
            $connectionOptions['friends'] = Yii::t('UserModule.base', 'Friends');
            $connectionOptions['pending_friends'] = Yii::t('UserModule.base', 'Pending Requests');
        }
        $this->addFilter('connection', [
            'title' => Yii::t('SpaceModule.base', 'Status'),
            'type' => 'dropdown',
            'options' => $connectionOptions,
            'sortOrder' => 400,
        ]);

        // Profile fields
        $profileFields = ProfileField::findAll(['directory_filter' => 1]);
        $profileFieldSortOrder = 1000;
        foreach ($profileFields as $profileField) {
            $this->initProfileFieldFilter($profileField, $profileFieldSortOrder);
            $profileFieldSortOrder += 10;
        }
    }

    private function initProfileFieldFilter(ProfileField $profileField, $sortOrder = 1000)
    {
        $profileFieldType = $profileField->getFieldType();

        if (!$profileFieldType) {
            return;
        }

        $definition = $profileFieldType->getFieldFormDefinition();
        $fieldType = isset($definition[$profileField->internal_name]['type']) ? $definition[$profileField->internal_name]['type'] : null;

        $filterData = [
            'title' => Html::encode(Yii::t($profileField->getTranslationCategory(), $profileField->title)),
            'type' => $fieldType,
            'sortOrder' => $sortOrder,
        ];

        switch ($fieldType) {
            case 'text':
            case 'dropdownlist':
                $filterData['type'] = 'widget';
                $filterData['widget'] = PeopleFilterPicker::class;
                $filterData['widgetOptions'] = [
                    'itemKey' => $profileField->internal_name
                ];
                break;

            default:
                // Skip not supported type
                return;
        }

        $this->addFilter('fields[' . $profileField->internal_name . ']', $filterData);
    }

    public static function getDefaultValue(string $filter): string
    {
        switch ($filter) {
            case 'sort':
                $defaultSorting = PeopleCard::config('defaultSorting');
                if ($defaultSorting == '' && !PeopleSettingsForm::isDefaultGroupDefined()) {
                    return 'lastlogin';
                }
                return $defaultSorting;
        }

        return '';
    }

}
