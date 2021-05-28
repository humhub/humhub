<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use humhub\components\Widget;
use humhub\libs\Html;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * SpacesFilters displays the filters on the directory spaces page
 *
 * @since 1.9
 * @author Luke
 */
class SpacesFilters extends Widget
{
    /**
     * @var array Filters
     */
    private $filters = [];

    public function init()
    {
        $this->initDefaultFilters();

        parent::init();

        ArrayHelper::multisort($this->filters, 'sortOrder');
    }

    private function initDefaultFilters()
    {
        $this->addFilter('keyword', [
                'title' => Yii::t('SpaceModule.base', 'Free text search in the directory (name, description, tags, etc.)'),
                'placeholder' => Yii::t('SpaceModule.base', 'search for spaces'),
                'type' => 'input',
                'wrapperClass' => 'col-md-6 form-search-filter-keyword',
                'afterInput' => Html::submitButton('<span class="fa fa-search"></span>', ['class' => 'form-button-search']),
                'sortOrder' => 100,
            ]);

        $this->addFilter('sort', [
                'title' => Yii::t('SpaceModule.base', 'Sorting'),
                'type' => 'dropdown',
                'options' => [
                    'name' => Yii::t('SpaceModule.base', 'Name'),
                    'newer' => Yii::t('SpaceModule.base', 'Newer spaces'),
                    'older' => Yii::t('SpaceModule.base', 'Older spaces'),
                ],
                'sortOrder' => 200,
            ]);

        $this->addFilter('connection', [
                'title' => Yii::t('SpaceModule.base', 'Connection'),
                'type' => 'dropdown',
                'options' => [
                    '' => Yii::t('SpaceModule.base', 'All'),
                    'member' => Yii::t('SpaceModule.base', 'Member'),
                    'follow' => Yii::t('SpaceModule.base', 'Follow'),
                ],
                'sortOrder' => 300,
            ]);
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('spacesFilters', ['spacesFilters' => $this]);
    }

    public function renderFilters(): string
    {
        $filtersHtml = '';
        foreach ($this->filters as $filter => $data) {
            $filtersHtml .= $this->render('spacesFilter', [
                'filter' => $filter,
                'data' => array_merge(self::getDefaultFilterData(), $data),
            ]);
        }
        return $filtersHtml;
    }

    public static function getDefaultFilterData(): array
    {
        return [
            'wrapperClass' => 'col-md-2',
            'titleClass' => 'form-search-field-info',
            'inputClass' => 'form-control form-search-filter',
            'beforeInput' => '',
            'afterInput' => '',
        ];
    }

    public static function renderFilterInput(string $filter, array $data): string
    {
        $inputOptions = ['class' => $data['inputClass']];

        if (isset($data['inputOptions'])) {
            $inputOptions = array_merge($inputOptions, $data['inputOptions']);
        }

        switch ($data['type']) {
            case 'dropdown':
                $inputOptions['data-action-change'] = 'directory.applyFilters';
                $inputHtml = Html::dropDownList($filter, self::getValue($filter), $data['options'], $inputOptions);
                break;

            case 'input':
            default:
                if (isset($data['placeholder'])) {
                    $inputOptions['placeholder'] = $data['placeholder'];
                }
                $inputHtml = Html::textInput($filter, self::getValue($filter), $inputOptions);
        }

        return $data['beforeInput'].$inputHtml.$data['afterInput'];
    }

    public function addFilter(string $filterKey, array $filterData)
    {
        $this->filters[$filterKey] = $filterData;
    }

    public static function getDefaultValue(string $filter): string
    {
        switch ($filter) {
            case 'sort':
                return 'name';
        }

        return '';
    }

    public static function getValue(string $filter)
    {
        $defaultValue = self::getDefaultValue($filter);

        return Yii::$app->request->get($filter, $defaultValue);
    }

}
