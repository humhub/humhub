<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/* @var $this \humhub\components\View */
/* @var $filters [] */

$sorting = Yii::$app->getModule('stream')->settings->get('defaultSort', 'c');

?>

<ul class="nav nav-tabs wallFilterPanel" id="filter" style="display: none;">
    <li class=" dropdown">
        <a class="stream-filter dropdown-toggle" data-toggle="dropdown"
           href="#"><?= Yii::t('ContentModule.widgets_views_stream', 'Filter'); ?> <b
                    class="caret"></b></a>
        <ul class="dropdown-menu">
            <?php foreach ($filters as $filterId => $filterTitle): ?>
                <li>
                    <a href="#" class="wallFilter" id="<?= $filterId; ?>">
                        <i class="fa fa-square-o"></i> <?= $filterTitle; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </li>
    <li class="dropdown">
        <a class="stream-sorting dropdown-toggle" data-toggle="dropdown"
           href="#"><?= Yii::t('ContentModule.widgets_views_stream', 'Sorting'); ?>
            <b class="caret"></b></a>
        <ul class="dropdown-menu">
            <li>
                <a href="#" class="wallSorting" id="sorting_c">
                    <i class="fa <?= ($sorting === 'c') ? 'fa-check-square-o' : 'fa-square-o' ?>"></i> <?= Yii::t('ContentModule.widgets_views_stream', 'Creation time'); ?>
                </a>
            </li>
            <li>
                <a href="#" class="wallSorting" id="sorting_u">
                    <i class="fa <?= ($sorting === 'u') ? 'fa-check-square-o' : 'fa-square-o' ?>"></i> <?= Yii::t('ContentModule.widgets_views_stream', 'Last update'); ?>
                </a>
            </li>
        </ul>
    </li>
</ul>