<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\export;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\data\BaseDataProvider;
use yii\di\Instance;
use yii\helpers\ArrayHelper;
use yii\i18n\Formatter;

/**
 * SpreadsheetExport allows export of data into PhpSpreadsheet
 * It supports exporting of the [[\yii\data\DataProviderInterface]] and [[\yii\db\QueryInterface]] instances.
 *
 * This class was originally developed by Paul Klimov <klimov.paul@gmail.com> and his
 * project csv-grid (https://github.com/yii2tech/csv-grid).
 *
 * Example:
 *
 * ```php
 * use humhub\components\export\SpreadsheetExport;
 * use yii\data\ArrayDataProvider;
 *
 * $exporter = new SpreadsheetExport([
 *     'dataProvider' => new ArrayDataProvider([
 *         'allModels' => [
 *             [
 *                 'name' => 'some name',
 *                 'price' => '9879',
 *             ],
 *             [
 *                 'name' => 'name 2',
 *                 'price' => '79',
 *             ],
 *         ],
 *     ]),
 *     'columns' => [
 *         [
 *             'attribute' => 'name',
 *         ],
 *         [
 *             'attribute' => 'price',
 *             'format' => 'decimal',
 *         ],
 *     ],
 * ]);
 * $exporter->export()->saveAs('/path/to/file.csv');
 * ```
 */
class SpreadsheetExport extends Component
{
    /**
     * @var \yii\data\DataProviderInterface the data provider for the view.
     * This property can be omitted in case [[query]] is set.
     */
    public $dataProvider;
    /**
     * @var \yii\db\QueryInterface the data source query.
     * Note: this field will be ignored in case [[dataProvider]] is set.
     */
    public $query;
    /**
     * @var array|Column[]
     */
    public $columns = [];
    /**
     * @var bool whether to show the header section of the sheet.
     */
    public $showHeader = true;
    /**
     * @var bool whether to show the footer section of the sheet.
     */
    public $showFooter = false;
    /**
     * @var bool enable autosize for xlsx/xls export.
     */
    public $autoSize = true;
    /**
     * @var string the HTML display when the content of a cell is empty.
     * This property is used to render cells that have no defined content,
     * e.g. empty footer or filter cells.
     *
     * Note that this is not used by the [[DataColumn]] if a data item is `null`. In that case
     * the [[nullDisplay]] property will be used to indicate an empty data value.
     */
    public $emptyCell = '';
    /**
     * @var string the text to be displayed when formatting a `null` data value.
     */
    public $nullDisplay = '';
    /**
     * @var array configuration for [[ExportResult]] instance created in process result.
     *
     * For example:
     *
     * ```php
     * [
     *     'forceArchive' => true
     * ]
     * ```
     *
     * @see ExportResult
     */
    public $resultConfig = [];
    /**
     * @var array|Formatter the formatter used to format model attribute values into displayable texts.
     * This can be either an instance of [[Formatter]] or an configuration array for creating the [[Formatter]]
     * instance. If this property is not set, the "formatter" application component will be used.
     */
    private $formatter;

    /**
     * @var int
     */
    private $row = 1;

    /**
     * Initializes the grid.
     * This method will initialize required property values and instantiate [[columns]] objects.
     */
    public function init()
    {
        parent::init();

        if ($this->dataProvider === null && $this->query !== null) {
            $this->dataProvider = new ActiveDataProvider([
                'query' => $this->query,
            ]);
        }

        if ($this->dataProvider instanceof BaseDataProvider) {
            $this->dataProvider->setPagination(false);
        }
    }

    /**
     * @return Formatter formatter instance
     * @throws \yii\base\InvalidConfigException
     */
    public function getFormatter()
    {
        if (!is_object($this->formatter)) {
            if ($this->formatter === null) {
                $this->formatter = Yii::$app->getFormatter();
            } else {
                $this->formatter = Instance::ensure($this->formatter, Formatter::class);
            }
        }
        return $this->formatter;
    }

    /**
     * @param array|Formatter $formatter
     */
    public function setFormatter($formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * Creates column objects and initializes them.
     * @param array $model list of single row model
     * @throws \yii\base\InvalidConfigException
     */
    protected function initColumns($model)
    {
        if (empty($this->columns)) {
            $this->guessColumns($model);
        }
        foreach ($this->columns as $i => $column) {
            if (is_string($column)) {
                $column = $this->createDataColumn($column);
            } else {
                $column = Yii::createObject(array_merge([
                    'class' => DataColumn::class,
                    'grid' => $this,
                ], $column));
            }
            if (!$column->visible) {
                unset($this->columns[$i]);
                continue;
            }
            $this->columns[$i] = $column;
        }
    }

    /**
     * This function tries to guess the columns to show from the given data
     * if [[columns]] are not explicitly specified.
     * @param array $model list of model
     */
    protected function guessColumns($model)
    {
        if (is_array($model) || is_object($model)) {
            foreach ($model as $name => $value) {
                $this->columns[] = (string)$name;
            }
        }
    }

    /**
     * Creates a [[DataColumn]] object based on a string in the format of "attribute:format:label".
     * @param string $text the column specification string
     * @return DataColumn the column instance
     * @throws InvalidConfigException if the column specification is invalid
     */
    protected function createDataColumn($text)
    {
        if (!preg_match('/^([^:]+)(:(\w*))?(:(.*))?$/', $text, $matches)) {
            throw new InvalidConfigException(
                'The column must be specified in the format of "attribute", '
                . '"attribute:format" or "attribute:format:label"',
            );
        }

        /** @var DataColumn $column */
        $column = Yii::createObject([
            'class' => DataColumn::class,
            'grid' => $this,
            'attribute' => $matches[1],
            'format' => isset($matches[3]) ? $matches[3] : 'text',
            'label' => isset($matches[5]) ? $matches[5] : null,
        ]);

        return $column;
    }

    /**
     * Performs data export.
     * @return ExportResult export result.
     * @throws \yii\base\InvalidConfigException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function export()
    {
        /** @var ExportResult $result */
        $result = Yii::createObject(array_merge([
            'class' => ExportResult::class,
        ], $this->resultConfig));

        $spreadsheet = $result->newSpreadsheet();

        $models = $this->dataProvider->getModels();
        $keys = $this->dataProvider->getKeys();

        $this->initColumns(reset($models));

        if ($this->showHeader) {
            $this->composeHeaderRow($spreadsheet);
        }

        foreach ($models as $index => $model) {
            $key = isset($keys[$index]) ? $keys[$index] : $index;
            $this->composeBodyRow($spreadsheet, $model, $key, $index);
        }

        if ($this->showFooter) {
            $this->composeFooterRow($spreadsheet);
        }

        if ($this->autoSize) {
            $this->applyAutoSize($spreadsheet);
        }

        $this->gc();

        return $result;
    }

    /**
     * Composes header row contents.
     * @param Spreadsheet $spreadsheet
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function composeHeaderRow($spreadsheet)
    {
        $worksheet = $spreadsheet->getActiveSheet();
        $row = $this->nextRow();

        foreach ($this->columns as $columnIndex => $column) {
            $coordinate = $this->getColumnLetter($columnIndex + 1) . $row;
            $worksheet->setCellValue(
                $coordinate,
                $column->renderHeaderCellContent(),
            );
        }
    }

    /**
     * Get the column letter based on column index.
     * @param int $columnIndex
     * @return string
     */
    protected function getColumnLetter($columnIndex)
    {
        $letters = range('A', 'Z');
        $letter = '';
        while ($columnIndex > 0) {
            $remainder = ($columnIndex - 1) % 26;
            $letter = $letters[$remainder] . $letter;
            $columnIndex = floor(($columnIndex - $remainder) / 26);
        }
        return $letter;
    }

    /**
     * Composes header row contents.
     * @param Spreadsheet $spreadsheet
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function composeFooterRow($spreadsheet)
    {
        $worksheet = $spreadsheet->getActiveSheet();
        $row = $this->nextRow();

        foreach ($this->columns as $columnIndex => $column) {
            $worksheet->setCellValueByColumnAndRow(
                $columnIndex + 1,
                $row,
                $column->renderFooterCellContent(),
            );
        }
    }

    /**
     * Composes body row contents.
     * @param Spreadsheet $spreadsheet
     * @param mixed $model the data model
     * @param mixed $key the key associated with the data model
     * @param int $index zero-based index of data model among the models array returned by [[GridView::dataProvider]].
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function composeBodyRow($spreadsheet, $model, $key, $index)
    {
        $worksheet = $spreadsheet->getActiveSheet();
        $row = $this->nextRow();

        foreach ($this->columns as $columnIndex => $column) {
            $coordinate = $this->getColumnLetter($columnIndex + 1) . $row;
            $value = $column->renderDataCellContent($model, $key, $index);
            $value = $this->sanitizeValue($value);

            if ($column->dataType !== null) {
                $worksheet->getCell($coordinate)->setValueExplicit($value, $column->dataType);
            } else {
                $worksheet->setCellValue($coordinate, $value);
            }

            if ($column->styles !== []) {
                $worksheet->getStyle($coordinate)->applyFromArray($column->styles);
            }
        }
    }

    /**
     * Sanitize value to prevent injection.
     */
    private function sanitizeValue(?string $value): ?string
    {
        if (
            empty($value)
            || !in_array(
                ucfirst(ArrayHelper::getValue($this->resultConfig, 'writerType', (new ExportResult())->writerType)),
                [IOFactory::WRITER_CSV, IOFactory::WRITER_XLSX, IOFactory::WRITER_XLS],
            )
        ) {
            return $value;
        }

        // Check for risky starting characters or formula-like values and prepend single quote
        if (strpbrk($value[0], '=+-@,;' . "\t" . "\r") !== false || preg_match('/^\d+[+\-*\/].+/', $value)) {
            $value = "'" . $value;
        }

        // Sanitize escaping quotes, wrapping in double quotes if needed
        if (strpbrk($value, "\"\n,") !== false) {
            $value = '"' . str_replace('"', '""', $value) . '"';
        }

        return $value;
    }

    /**
     * Enable AutoSize for Export
     * @param Spreadsheet $spreadsheet
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function applyAutoSize($spreadsheet)
    {
        $worksheet = $spreadsheet->getActiveSheet();
        foreach ($this->columns as $columnIndex => $column) {
            $worksheet->getColumnDimensionByColumn($columnIndex + 1)->setAutoSize(true);
        }
    }

    /**
     * @return int
     */
    protected function nextRow()
    {
        return $this->row++;
    }

    /**
     * Performs PHP memory garbage collection.
     */
    protected function gc()
    {
        if (!gc_enabled()) {
            gc_enable();
        }
        gc_collect_cycles();
    }
}
