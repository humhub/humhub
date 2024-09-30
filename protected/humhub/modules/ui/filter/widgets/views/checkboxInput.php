<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

use humhub\helpers\Html;
use humhub\modules\ui\view\components\View;

/* @var $this View */
/* @var $options array */
/* @var $title string */
/* @var $value bool */
/* @var $checked bool */
/* @var $iconInActive bool */
/* @var $iconActive bool */
?>

<?= Html::beginTag('a', $options) ?>
<i class="fa  <?= ($checked) ? $iconActive : $iconInActive ?>"></i> <?= $title ?>
<?= Html::endTag('a') ?>
