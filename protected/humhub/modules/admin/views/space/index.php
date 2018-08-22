<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use humhub\modules\admin\widgets\SpaceGridView;
use humhub\modules\admin\grid\SpaceActionColumn;
use humhub\modules\admin\grid\SpaceTitleColumn;
use humhub\modules\admin\grid\SpaceImageColumn;
use humhub\modules\admin\models\SpaceSearch;
?>

<?= Html::a('<i class="fa fa-plus" aria-hidden="true"></i>&nbsp;&nbsp;' . Yii::t('AdminModule.space', 'Add new space'), ['/space/create'], ['class' => 'btn btn-sm btn-success pull-right', 'data-target' => '#globalModal']); ?>

<h4><?= Yii::t('AdminModule.views_space_index', 'Overview'); ?></h4>
<div class="help-block">
    <?= Yii::t('AdminModule.views_space_index', 'This overview contains a list of each space with actions to view, edit and delete spaces.'); ?>
</div>

<br />
<?php $form = ActiveForm::begin(['method' => 'get']); ?>
<div class="row">
    <div class="col-md-8">
        <div class="input-group">
            <?= Html::activeTextInput($searchModel, 'freeText', ['class' => 'form-control', 'placeholder' => Yii::t('AdminModule.space', 'Search by name, description, id or owner.')]); ?>
            <span class="input-group-btn">
                <button class="btn btn-default" type="submit"><i class="fa fa-search"></i></button>
            </span>
        </div>     
    </div>
    <div class="col-md-4"  style="padding-left:0px">
        <?= Html::activeDropDownList($searchModel, 'visibility', SpaceSearch::getVisibilityAttributes(), ['class' => 'form-control', 'onchange' => 'this.form.submit()']); ?>
    </div>
</div>
<?php ActiveForm::end(); ?>


<div class="table-responsive">
    <?=
    SpaceGridView::widget([
        'dataProvider' => $dataProvider,
        'summary' => '',
        'columns' => [
            ['class' => SpaceImageColumn::class],
            ['class' => SpaceTitleColumn::class],
            'memberCount',
            ['class' => \humhub\modules\user\grid\ImageColumn::class, 'userAttribute' => 'ownerUser'],
            [
                'attribute' => 'ownerUser.profile.lastname',
                'class' => \humhub\modules\user\grid\DisplayNameColumn::class,
                'userAttribute' => 'ownerUser',
                'label' => 'Owner'
            ],
            ['class' => SpaceActionColumn::class],
        ],
    ]);
    ?>
</div>
