<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\helpers\Html;
use humhub\modules\ui\view\components\View;

/* @var $this View */
/* @var $options [] */
/* @var $selection [] */
/* @var $items [] */
?>
<div class="form-group">
    <?= Html::dropDownList(null, $selection, $items, $options) ?>
</div>
