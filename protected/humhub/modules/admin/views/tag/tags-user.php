<?php

use humhub\modules\admin\grid\TagActionColumn;
use humhub\modules\admin\grid\TagImageColumn;
use humhub\modules\admin\grid\TagTitleColumn;
use humhub\modules\admin\widgets\TagGridView;
use yii\data\ActiveDataProvider;

/** @var ActiveDataProvider $dataProvider */
?>

<h4><?= Yii::t('AdminModule.views_tag_user', 'Overview'); ?></h4>
<div class="help-block">
    <?= Yii::t('AdminModule.views_tag_user', 'This overview contains the list of user tags with delete action.'); ?>
</div>

<div class="table-responsive">
    <?=
    TagGridView::widget([
        'dataProvider' => $dataProvider,
        'summary' => '',
        'columns' => [
            ['class' => TagTitleColumn::class,],
            ['class' => TagImageColumn::class],
            ['class' => TagActionColumn::class],
        ],
    ]);
    ?>
</div>
