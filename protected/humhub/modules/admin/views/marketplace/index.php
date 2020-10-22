<?php

use humhub\modules\admin\grid\MarketplaceActionColumn;
use humhub\modules\admin\grid\MarketplaceSpaceColumn;
use humhub\modules\admin\grid\MarketplaceStatusColumn;
use humhub\modules\admin\grid\MarketplaceTitleColumn;
use humhub\modules\admin\widgets\CategoryGridView;
use yii\data\ActiveDataProvider;

/** @var ActiveDataProvider $dataProvider */
?>

<h4><?= Yii::t('AdminModule.views_marketplace_index', 'Overview'); ?></h4>
<div class="help-block">
    <?= Yii::t('AdminModule.views_marketplace_index', 'This overview contains a list of space marketplaces with action edit.'); ?>
</div>

<div class="table-responsive">
    <?=
    CategoryGridView::widget([
        'dataProvider' => $dataProvider,
        'summary' => '',
        'columns' => [
            ['class' => MarketplaceTitleColumn::class,],
            ['class' => MarketplaceSpaceColumn::class],
            ['class' => MarketplaceStatusColumn::class],
            ['class' => MarketplaceActionColumn::class],
        ],
    ]);
    ?>
</div>
