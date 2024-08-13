<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\export;

use yii\base\BaseObject;

/**
 * Column is the base class of all [[SpreadsheetExport]] column classes.
 *
 * This class was originally developed by Paul Klimov <klimov.paul@gmail.com> and his
 * project csv-grid (https://github.com/yii2tech/csv-grid).
 */
class Column extends BaseObject
{
    /**
     * @var SpreadsheetExport the grid view object that owns this column.
     */
    public $grid;
    /**
     * @var string the header cell content. Note that it will not be HTML-encoded.
     */
    public $header;
    /**
     * @var string the footer cell content. Note that it will not be HTML-encoded.
     */
    public $footer;
    /**
     * @var callable This is a callable that will be used to generate the content of each cell.
     * The signature of the function should be the following: `function ($model, $key, $index, $column)`.
     * Where `$model`, `$key`, and `$index` refer to the model, key and index of the row currently being rendered
     * and `$column` is a reference to the [[Column]] object.
     */
    public $content;
    /**
     * @var bool whether this column is visible. Defaults to true.
     */
    public $visible = true;
    /**
     * @var string|null specify data type
     * @see https://phpspreadsheet.readthedocs.io/en/develop/topics/accessing-cells/#excel-datatypes
     */
    public $dataType = null;
    /**
     * @var array containing style information
     * @see https://phpspreadsheet.readthedocs.io/en/develop/topics/recipes/#styles
     */
    public $styles = [];

    /**
     * Renders the header cell content.
     * The default implementation simply renders [[header]].
     * This method may be overridden to customize the rendering of the header cell.
     * @return string the rendering result
     */
    public function renderHeaderCellContent()
    {
        return trim($this->header) !== '' ? $this->header : $this->grid->emptyCell;
    }

    /**
     * Renders the footer cell content.
     * The default implementation simply renders [[footer]].
     * This method may be overridden to customize the rendering of the footer cell.
     * @return string the rendering result
     */
    public function renderFooterCellContent()
    {
        return trim($this->footer) !== '' ? $this->footer : $this->grid->emptyCell;
    }

    /**
     * Renders the data cell content.
     * @param mixed $model the data model
     * @param mixed $key the key associated with the data model
     * @param int $index zero-based index of data model among models array returned by [[GridView::dataProvider]].
     * @return string the rendering result
     */
    public function renderDataCellContent($model, $key, $index)
    {
        if ($this->content !== null) {
            return call_user_func($this->content, $model, $key, $index, $this);
        } else {
            return $this->grid->emptyCell;
        }
    }
}
