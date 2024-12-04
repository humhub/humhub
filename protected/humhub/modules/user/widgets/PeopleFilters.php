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
use humhub\modules\user\components\PeopleQuery;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\ProfileField;
use humhub\modules\user\Module;
use Yii;
use yii\db\Expression;

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

    public ?PeopleQuery $query = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        /* @var $module Module */
        $module = Yii::$app->getModule('user');
        if (!$module->peopleEnableNestedFilters) {
            // Disable the reducing filter options to currently filtered users
            $this->query = null;
        }

        parent::init();
    }

    protected function initDefaultFilters()
    {
        // Keyword
        $this->addFilter('keyword', [
            'title' => Yii::t('UserModule.base', 'Find people by their profile data or user tags'),
            'placeholder' => Yii::t('UserModule.base', 'Search...'),
            'type' => 'text',
            'inputOptions' => ['autocomplete' => 'off'],
            'wrapperClass' => 'col-md-6 form-search-filter-keyword',
            'afterInput' => Html::submitButton('<span class="fa fa-search"></span>', ['class' => 'form-button-search']),
            'sortOrder' => 100,
        ]);

        // Group
        $groupOptions = $this->getGroupOptions();
        if (!empty($groupOptions)) {
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
        ];
        if (!Yii::$app->getModule('user')->disableFollow) {
            $connectionOptions['followers'] = Yii::t('UserModule.base', 'Followers');
            $connectionOptions['following'] = Yii::t('UserModule.base', 'Following');
        }
        if (Yii::$app->getModule('friendship')->settings->get('enable')) {
            $connectionOptions['friends'] = Yii::t('UserModule.base', 'Friends');
            $connectionOptions['pending_friends'] = Yii::t('UserModule.base', 'Pending Requests');
        }
        if (count($connectionOptions) > 1) {
            $this->addFilter('connection', [
                'title' => Yii::t('SpaceModule.base', 'Status'),
                'type' => 'dropdown',
                'options' => $connectionOptions,
                'sortOrder' => 400,
            ]);
        }

        // Profile fields
        $profileFields = ProfileField::find()
            ->where(['directory_filter' => 1])
            ->orderBy(['sort_order' => SORT_ASC]);
        $profileFieldSortOrder = 1000;
        foreach ($profileFields->each() as $profileField) {
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
                $filterData['inputOptions'] = ['data-dropdown-auto-width' => 'true'];
                $filterData['widgetOptions'] = [
                    'itemKey' => $profileField->internal_name,
                    'query' => $this->query,
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

    protected function getGroupOptions(): array
    {
        $options = ['' => Yii::t('UserModule.base', 'Any')];

        if ($this->query instanceof PeopleQuery && $this->query->isFiltered()) {
            $query = clone $this->query;

            $groups = $query
                ->leftJoin('group_user AS fgu', 'fgu.user_id = user.id')
                ->leftJoin('group', 'fgu.group_id = group.id')
                ->select(['group.id', 'group.name'])
                ->andWhere(['show_at_directory' => 1])
                ->andWhere(['IS NOT', 'group.id', new Expression('NULL')])
                ->offset(null)
                ->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])
                ->asArray()
                ->all();

            if (empty($groups)) {
                return [];
            }

            foreach ($groups as $group) {
                $options[$group['id']] = $group['name'];
            }

            return $options;
        }

        $groups = Group::find()
            ->where(['show_at_directory' => 1])
            ->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])
            ->all();

        if (empty($groups)) {
            return [];
        }

        /* @var Group[] $groups */
        foreach ($groups as $group) {
            $options[$group->id] = $group->name;
        }

        return $options;
    }

}
