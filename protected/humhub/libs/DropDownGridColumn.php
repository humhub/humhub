<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use humhub\helpers\Html;
use Yii;
use yii\grid\DataColumn;
use yii\web\JsExpression;

/**
 * DropDown Grid Column
 *
 * @author luke
 */
class DropDownGridColumn extends DataColumn
{
    /**
     * @var array list of attributes which should be aditionally submitted (e.g. id)
     */
    public $submitAttributes = ['id'];

    /**
     * @var array html options
     */
    public $htmlOptions = [];

    /**
     * @var array dropdown options
     */
    public $dropDownOptions = [];

    /**
     * @var array ajax options
     */
    public $ajaxOptions = [];
    public $readonly = false;

    /**
     * @var string|callable|null the accessible name of each dropdown, e.g. the title of its row;
     * a callable gets `($model, $key, $index, $column)` (WCAG 4.1.2)
     * @since 1.20
     */
    public $ariaLabel;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!isset($this->ajaxOptions['type'])) {
            $this->ajaxOptions['type'] = 'POST';
        }

        $this->ajaxOptions['data'] = new JsExpression('data');
        $this->ajaxOptions['success'] = new JsExpression('function() { humhub.modules.ui.status.success("' . Yii::t('base', 'Saved') . '"); }');
        $this->grid->view->registerJs("$('.editableCell').change(function() {
            data = {};
            data[$(this).attr('name')] = $(this).val();
            submitAttributes = $(this).data('submit-attributes').split(', ');
            for (var i in submitAttributes) {
                data[submitAttributes[i]] = $(this).data('attribute'+i);
            }
            data['dropDownColumnSubmit'] = true;
            $.ajax(" . \yii\helpers\Json::encode($this->ajaxOptions) . ");
        });");

        return parent::init();
    }

    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        $htmlOptions = $this->htmlOptions;
        Html::addCssClass($htmlOptions, ['editableCell', 'form-control']);

        // Fixes: https://github.com/yiisoft/yii2/issues/19534
        $htmlOptions['strict'] = true;

        // We need to number the submit attributes because data attribute is not case sensitive
        $htmlOptions['data-submit-attributes'] = implode(', ', $this->submitAttributes);
        $i = 0;
        foreach ($this->submitAttributes as $attribute) {
            $htmlOptions['data-attribute' . $i] = $model[$attribute];
            $i++;
        }

        $ariaLabel = is_callable($this->ariaLabel)
            ? call_user_func($this->ariaLabel, $model, $key, $index, $this)
            : $this->ariaLabel;
        if ($ariaLabel !== null && $ariaLabel !== '') {
            $htmlOptions['aria-label'] = $ariaLabel;
        }

        if (is_array($this->dropDownOptions)) {
            $options = $this->dropDownOptions;
        } else {
            $options = $model[$this->dropDownOptions];
        }

        $inputName = (is_array($model)) ? $this->attribute : Html::getInputName($model, $this->attribute);

        $readonly = $this->readonly;
        if (!is_bool($readonly)) {
            $readonly = call_user_func($this->readonly, $model, $key, $index, $this);
        }

        if ($readonly) {
            if (isset($options[$model[$this->attribute]])) {
                return Html::dropDownList($inputName, $model[$this->attribute], $options, array_merge($htmlOptions, ['readonly' => true, 'disabled' => true]));
            }
            return $model[$this->attribute];
        }

        return Html::dropDownList($inputName, $model[$this->attribute], $options, $htmlOptions);
    }
}
