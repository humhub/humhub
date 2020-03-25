<?php

use humhub\modules\admin\grid\CategoryActionColumn;
use humhub\modules\admin\grid\CategoryImageColumn;
use humhub\modules\admin\grid\CategoryTitleColumn;
use humhub\modules\admin\widgets\CategoryGridView;
use yii\data\ActiveDataProvider;

/** @var ActiveDataProvider $dataProvider */
?>

<h4><?= Yii::t('AdminModule.views_category_funding-index', 'Overview'); ?></h4>
<div class="help-block">
    <?= Yii::t('AdminModule.views_category_funding-index', 'This overview contains a list of category space with action delete.'); ?>
</div>

<div class="table-responsive">
    <?=
    CategoryGridView::widget([
        'dataProvider' => $dataProvider,
        'summary' => '',
        'columns' => [
            ['class' => CategoryTitleColumn::class,],
            ['class' => CategoryImageColumn::class],
            ['class' => CategoryActionColumn::class],
        ],
    ]);
    ?>
</div>
