<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\export;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQueryInterface;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;

/**
 * DataColumn is the default column type for the [[SpreadsheetExport]] widget.
 *
 * This class was originally developed by Paul Klimov <klimov.paul@gmail.com> and his
 * project csv-grid (https://github.com/yii2tech/csv-grid).
 */
class DataColumn extends Column
{
    /**
     * @var string the attribute name associated with this column. When neither [[content]] nor [[value]]
     * is specified, the value of the specified attribute will be retrieved from each data model and displayed.
     *
     * Also, if [[label]] is not specified, the label associated with the attribute will be displayed.
     */
    public $attribute;
    /**
     * @var string label to be displayed in the [[header|header cell]].
     * If it is not set and the models provided by the GridViews data provider are instances
     * of [[\yii\db\ActiveRecord]], the label will be determined using [[\yii\db\ActiveRecord::getAttributeLabel()]].
     * Otherwise [[\yii\helpers\Inflector::camel2words()]] will be used to get a label.
     */
    public $label;
    /**
     * @var string|\Closure anonymous function or a string that is used to determine the value for the current column.
     *
     * If this is an anonymous function, it will be called for each row and the return value will be used as the
     * value to display for every data model. The signature of this function should be:
     * `function ($model, $key, $index, $column)`.
     * Where `$model`, `$key`, and `$index` refer to the model, key and index of the row currently being rendered
     * and `$column` is a reference to the [[DataColumn]] object.
     *
     * You may also set this property to a string representing the attribute name to be displayed in this column.
     *
     * If this is not set, `$model[$attribute]` will be used to obtain the value, where `$attribute`
     * is the value of [[attribute]].
     */
    public $value;
    /**
     * @var string|array in which format should the value of each data model be displayed as
     * (e.g. `"raw"`, `"text"`, `"html"`, `['date', 'php:Y-m-d']`). Supported formats are determined by
     * the [[GridView::formatter|formatter]] used by he [[GridView]]. Default format is "text" which will format
     * the value as an HTML-encoded plain text when [[\yii\i18n\Formatter]] is used as
     * the [[GridView::$formatter|formatter]] of the GridView.
     */
    public $format = 'text';


    /**
     * @inheritdoc
     */
    public function renderHeaderCellContent()
    {
        if ($this->header !== null || $this->label === null && $this->attribute === null) {
            return parent::renderHeaderCellContent();
        }

        $provider = $this->grid->dataProvider;

        if ($this->label === null) {
            if ($provider instanceof ActiveDataProvider && $provider->query instanceof ActiveQueryInterface) {
                /* @var $model Model */
                $model = new $provider->query->modelClass;
                $label = $model->getAttributeLabel($this->attribute);
            } else {
                $models = $provider->getModels();
                if (($model = reset($models)) instanceof Model) {
                    /* @var $model Model */
                    $label = $model->getAttributeLabel($this->attribute);
                } else {
                    $label = Inflector::camel2words($this->attribute);
                }
            }
        } else {
            $label = $this->label;
        }

        return $label;
    }

    /**
     * Returns the data cell value.
     * @param mixed $model the data model
     * @param mixed $key the key associated with the data model
     * @param integer $index zero-based index of data model among models array returned by [[GridView::dataProvider]].
     * @return string the data cell value
     */
    public function getDataCellValue($model, $key, $index)
    {
        if ($this->value !== null) {
            if (is_string($this->value)) {
                return ArrayHelper::getValue($model, $this->value);
            } else {
                return call_user_func($this->value, $model, $key, $index, $this);
            }
        } elseif ($this->attribute !== null) {
            return ArrayHelper::getValue($model, $this->attribute);
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function renderDataCellContent($model, $key, $index)
    {
        if ($this->content === null) {
            $value = $this->getDataCellValue($model, $key, $index);
            if ($value === null) {
                return $this->grid->nullDisplay;
            }
            return $this->grid->formatter->format($value, $this->format);
        } else {
            return parent::renderDataCellContent($model, $key, $index);
        }
    }
}
