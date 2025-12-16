<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\grid;

use yii\grid\ActionColumn;

/**
 * ApprovalActionColumn
 */
class ApprovalActionColumn extends ActionColumn
{
    // On mobile, display the buttons as a grid, with 2 buttons on each line
    public $contentOptions = [
        'class' => 'd-grid d-md-flex justify-content-end gap-1',
        'style' => 'grid-template-columns: repeat(2, 1fr);',
    ];

    public $template = '{view} {sendMessage} {update} {delete}';
}
