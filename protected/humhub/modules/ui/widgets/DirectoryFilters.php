<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\widgets;

use humhub\components\Widget;
use humhub\libs\Html;
use humhub\modules\ui\form\widgets\DatePicker;
use humhub\widgets\Button;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * DirectoryFilters displays the filters on the directory people/spaces/modules pages
 *
 * @since 1.9
 * @author Luke
 */
abstract class DirectoryFilters extends Widget
{
    /**
     * @var array Filters
     */
    public $filters = [];

    /**
     * @var string Main page URL, used to reset and submit a form with filters
     */
    public $pageUrl;

    /**
     * @var bool True - if paganation is used for the filtered results
     * @since 1.11
     */
    public $paginationUsed = true;

    /**
     * @var array|null Additional form data, can be used for JavaScript actions:
     *      'action-url' - URL to submit the filters form by AJAX request
     * @since 1.16
     */
    public ?array $data = null;

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->initDefaultFilters();

        $this->addFilter('reset', [
            'type' => 'info',
            'wrapperClass' => 'col-md-2 form-search-without-info',
            'info' => Html::a(Yii::t('UiModule.base', 'Reset filters'), [$this->pageUrl], ['class' => 'form-search-reset']),
            'sortOrder' => 10000,
        ]);

        parent::init();

        ArrayHelper::multisort($this->filters, 'sortOrder');
    }

    abstract protected function initDefaultFilters();

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('@humhub/modules/ui/widgets/views/directoryFilters', [
            'directoryFilters' => $this,
            'options' => $this->getOptions(),
        ]);
    }

    protected function getOptions(): array
    {
        $options = ['class' => 'form-search'];

        if (is_array($this->data)) {
            $options['data'] = $this->data;
        }

        return $options;
    }

    public function renderFilters(): string
    {
        $filtersHtml = '';
        foreach ($this->filters as $filter => $data) {
            $data = array_merge(self::getDefaultFilterData(), $data);
            $filterInput = $this->renderFilterInput($filter, $data);

            if ($filterInput !== $data['beforeInput'] . $data['afterInput']) {
                $filtersHtml .= $this->render('@humhub/modules/ui/widgets/views/directoryFilter', [
                    'data' => $data,
                    'filterInput' => $filterInput,
                ]);
            }
        }
        return $filtersHtml;
    }

    public static function getDefaultFilterData(): array
    {
        return [
            'wrapperClass' => 'col-md-2',
            'titleClass' => 'form-search-field-info',
            'inputClass' => 'form-control',
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
                $inputOptions['data-action-change'] = 'cards.applyFilters';
                $inputOptions['options'] = ['separator' => ['disabled' => '']];
                if (isset($data['options'][''])) {
                    // Enable the reset feature if this filter has an empty value option
                    $inputOptions['data-allow-clear'] = 'true';
                    $inputOptions['data-placeholder'] = Yii::t('UiModule.base', 'Select');
                }
                $inputHtml = Html::dropDownList($filter, self::getValue($filter), $data['options'], $inputOptions);
                break;

            case 'tags':
                $inputHtml = '';
                if (empty($data['tags'])) {
                    break;
                }

                $activeTags = self::getValue($filter);
                $filterOptions = empty($data['multiple']) ? [] : ['data-multiple' => 1];
                $inputHtml .= Html::hiddenInput($filter, $activeTags, $filterOptions);
                $activeTags = empty($activeTags) ? [] : explode(',', $activeTags);

                foreach ($data['tags'] as $tagKey => $tagLabel) {
                    $isActiveTag = (empty($tagKey) && empty($activeTags))
                        || in_array($tagKey, $activeTags);

                    $inputHtml .= Button::none($tagLabel)
                        ->options(['class' => 'btn btn-sm btn-primary' . ($isActiveTag ? ' active' : '')])
                        ->action('cards.selectTag')
                        ->options([
                            'data-filter' => $filter,
                            'data-tag' => $tagKey,
                        ]);
                }
                break;

            case 'info':
                $inputHtml = $data['info'];
                break;

            case 'widget':
                $inputOptions['data-action-change'] = 'cards.applyFilters';
                $options = ['name' => $filter, 'options' => $inputOptions];
                if (isset($data['widgetOptions']) && is_array($data['widgetOptions'])) {
                    $options = array_merge($options, $data['widgetOptions']);
                }
                $inputHtml = $data['widget']::widget($options);
                break;

            case 'date':
                $format = $data['format'] ?? 'short';
                $value = self::getValue($filter);
                $inputHtml = DatePicker::widget([
                    'name' => $filter,
                    'value' => empty($value) ? '' : Yii::$app->formatter->asDate($value, $format),
                    'dateFormat' => $format,
                ]);
                break;

            case 'input':
            case 'text':
            default:
                if (isset($data['placeholder'])) {
                    $inputOptions['placeholder'] = $data['placeholder'];
                }
                $inputHtml = Html::textInput($filter, self::getValue($filter), $inputOptions);
        }

        return $data['beforeInput'] . $inputHtml . $data['afterInput'];
    }

    public function addFilter(string $filterKey, array $filterData)
    {
        $this->filters[$filterKey] = $filterData;
    }

    public function removeFilter(string $filterKey)
    {
        unset($this->filters[$filterKey]);
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
