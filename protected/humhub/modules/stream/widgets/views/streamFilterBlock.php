<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/* @var $this \humhub\components\View */
/* @var $title string */
/* @var $filters array */
/* @var $block array */
/* @var $checked boolean */
?>

<strong><?= $title ?></strong>
<ul>
    <?php foreach ($block as $filterId): ?>
        <li>
            <a href="#" class="wallFilter" id="<?= $filterId; ?>">
                <i class="fa  <?= ($checked) ? 'fa-check-square-o' : 'fa-square-o'?>"></i> <?= $filters[$filterId]; ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>
