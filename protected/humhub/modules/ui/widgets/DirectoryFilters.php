<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\widgets;

use humhub\components\Widget;
use humhub\libs\Html;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * DirectoryFilters displays the filters on the directory people/spaces pages
 *
 * @since 1.9
 * @author Luke
 */
abstract class DirectoryFilters extends Widget
{
    /**
     * @var array Filters
     */
    protected $filters = [];

    /**
     * @var string Main page URL, used to reset and submit a form with filters
     */
    public $pageUrl;

    public function init()
    {
        $this->initDefaultFilters();

        parent::init();

        ArrayHelper::multisort($this->filters, 'sortOrder');
    }

    abstract protected function initDefaultFilters();

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('@humhub/modules/ui/widgets/views/directoryFilters', ['directoryFilters' => $this]);
    }

    public function renderFilters(): string
    {
        $filtersHtml = '';
        foreach ($this->filters as $filter => $data) {
            $filtersHtml .= $this->render('@humhub/modules/ui/widgets/views/directoryFilter', [
                'directoryFilters' => $this,
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

    public function renderFilterInput(string $filter, array $data): string
    {
        $inputOptions = ['class' => $data['inputClass']];

        if (isset($data['inputOptions'])) {
            $inputOptions = array_merge($inputOptions, $data['inputOptions']);
        }

        switch ($data['type']) {
            case 'dropdown':
            case 'dropdownlist':
                $inputOptions['data-action-change'] = 'directory.applyFilters';
                $inputHtml = Html::dropDownList($filter, self::getValue($filter), $data['options'], $inputOptions);
                break;

            case 'input':
            case 'text':
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
        return '';
    }

    public static function getValue(string $filter)
    {
        $defaultValue = static::getDefaultValue($filter);

        if (preg_match('/^(.+?)\[(.+?)\]$/', $filter, $arrayMatch)) {
            $array = Yii::$app->request->get($arrayMatch[1]);
            return isset($array[$arrayMatch[2]]) ? $array[$arrayMatch[2]] : $defaultValue;
        }

        return Yii::$app->request->get($filter, $defaultValue);
    }

}
