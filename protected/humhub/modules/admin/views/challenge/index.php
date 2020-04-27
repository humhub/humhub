<?php

use humhub\modules\admin\grid\ChallengeActionColumn;
use humhub\modules\admin\grid\ChallengeSpaceColumn;
use humhub\modules\admin\grid\ChallengeStatusColumn;
use humhub\modules\admin\grid\ChallengeTitleColumn;
use humhub\modules\admin\widgets\CategoryGridView;
use yii\data\ActiveDataProvider;

/** @var ActiveDataProvider $dataProvider */
?>

<h4><?= Yii::t('AdminModule.views_challenge_index', 'Overview'); ?></h4>
<div class="help-block">
    <?= Yii::t('AdminModule.views_challenge_index', 'This overview contains a list of space challenges with action edit.'); ?>
</div>

<div class="table-responsive">
    <?=
    CategoryGridView::widget([
        'dataProvider' => $dataProvider,
        'summary' => '',
        'columns' => [
            ['class' => ChallengeTitleColumn::class,],
            ['class' => ChallengeSpaceColumn::class],
            ['class' => ChallengeStatusColumn::class],
            ['class' => ChallengeActionColumn::class],
        ],
    ]);
    ?>
</div>
