<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\space\components\SpacesQuery;
use humhub\modules\space\widgets\SpacesCard;
use humhub\modules\space\widgets\SpacesFilters;
use humhub\modules\user\assets\PeopleAsset;
use humhub\widgets\LinkPager;
use yii\web\View;

/* @var $this View */
/* @var $spaces SpacesQuery */

PeopleAsset::register($this);
?>
<div class="panel panel-default">

    <div class="panel-heading">
        <?= Yii::t('SpaceModule.base', '<strong>Space</strong> directory'); ?>
    </div>

    <div class="panel-body">
        <?= SpacesFilters::widget(); ?>
    </div>

</div>

<div class="row">
    <?php if (!$spaces->exists()): ?>
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-body">
                <?= Yii::t('SpaceModule.base', 'No spaces found!'); ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php foreach ($spaces->all() as $space) : ?>
        <?= SpacesCard::widget(['space' => $space]); ?>
    <?php endforeach; ?>
</div>

<div class="pagination-container">
    <?= LinkPager::widget(['pagination' => $spaces->pagination]); ?>
</div>
