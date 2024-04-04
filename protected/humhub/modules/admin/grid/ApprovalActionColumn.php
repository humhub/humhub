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
    public $template = '{view} {sendMessage} {update} {delete}';
}
