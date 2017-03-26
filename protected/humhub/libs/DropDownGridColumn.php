<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use Yii;
use yii\grid\DataColumn;
use yii\helpers\Json;
use yii\helpers\Html;
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
    public $ajaxOptions = array();
    public $readonly = false;

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
        if (isset($this->htmlOptions['class'])) {
            $this->htmlOptions['class'] .= 'editableCell form-control';
        } else {
            $this->htmlOptions['class'] = 'editableCell form-control';
        }

        // We need to number the submit attributes because data attribute is not case sensitive
        $this->htmlOptions['data-submit-attributes'] = implode(', ', $this->submitAttributes);
        $i = 0;
        foreach ($this->submitAttributes as $attribute) {
            $this->htmlOptions['data-attribute' . $i] = $model[$attribute];
            $i++;
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
                return Html::dropDownList($inputName, $model[$this->attribute], $options, array_merge($this->htmlOptions, ['readonly' => true, 'disabled' => true]));
            }
            return $model[$this->attribute];
        }

        return Html::dropDownList($inputName, $model[$this->attribute], $options, $this->htmlOptions);
    }

}
