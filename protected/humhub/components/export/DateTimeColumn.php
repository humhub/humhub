<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\export;

use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

/**
 * DateTimeColumn exports DateTime values to [[SpreadsheetExport]] widget.
 */
class DateTimeColumn extends DataColumn
{
    /**
     * @var array containing style information
     * @see https://phpspreadsheet.readthedocs.io/en/develop/topics/recipes/#styles
     */
    public $styles = [
        'numberFormat' => [
            'formatCode' => NumberFormat::FORMAT_DATE_DATETIME
        ]
    ];

    /**
     * @inheritdoc
     */
    public function renderDataCellContent($model, $key, $index)
    {
        $value = Date::PHPToExcel(parent::renderDataCellContent($model, $key, $index));
        return $value === false ? null : $value;
    }
}
