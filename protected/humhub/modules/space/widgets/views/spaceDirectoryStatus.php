<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/* @var $text string */

use humhub\widgets\bootstrap\Badge;

?>

<?= Badge::primary($text)->cssClass('card-status') ?>
