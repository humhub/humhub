<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

use yii\helpers\Html;

/* @var $this \humhub\components\View */
/* @var $options array */
/* @var $title string */
/* @var $value boolean */
/* @var $checked boolean */
/* @var $iconInActive boolean */
/* @var $iconActive boolean */
?>

<?= Html::beginTag('a', $options) ?>
<i class="fa  <?= ($checked) ? $iconActive : $iconInActive ?>"></i> <?= $title ?>
<?= Html::endTag('a') ?>

