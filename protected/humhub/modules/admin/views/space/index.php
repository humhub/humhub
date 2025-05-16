<?php

use humhub\helpers\Html;
use humhub\modules\admin\grid\SpaceActionColumn;
use humhub\modules\admin\grid\SpaceImageColumn;
use humhub\modules\admin\grid\SpaceTitleColumn;
use humhub\modules\admin\models\SpaceSearch;
use humhub\modules\admin\widgets\SpaceGridView;
use humhub\modules\space\permissions\CreatePrivateSpace;
use humhub\modules\space\permissions\CreatePublicSpace;
use humhub\modules\user\grid\DisplayNameColumn;
use humhub\modules\user\grid\ImageColumn;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\modal\ModalButton;
use yii\helpers\Url;

/* @var $searchModel SpaceSearch */
?>

<?php if (Yii::$app->user->can([CreatePublicSpace::class, CreatePrivateSpace::class])) : ?>
    <?= ModalButton::success(Yii::t('AdminModule.space', 'Add new space'))->load(['/space/create'])
        ->icon('add')->right()->sm() ?>
<?php endif; ?>

<h4><?= Yii::t('AdminModule.space', 'Overview') ?></h4>
<div class="text-body-secondary">
    <?= Yii::t('AdminModule.space', 'This overview contains a list of each space with actions to view, edit and delete spaces.'); ?>
</div>

<br/>
<?php $form = ActiveForm::begin(['method' => 'get', 'action' => Url::to(['/admin/space'])]); ?>
<div class="row">
    <div class="col-lg-8">
        <div class="input-group">
            <?= Html::activeTextInput($searchModel, 'freeText', ['class' => 'form-control', 'placeholder' => Yii::t('AdminModule.space', 'Search by name, description, id or owner.')]); ?>
            <button class="btn btn-light" type="submit"><i class="fa fa-search"></i></button>
        </div>
    </div>
    <div class="col-lg-4 spacesearch-visibilities">
        <?= Html::activeDropDownList($searchModel, 'visibility', SpaceSearch::getVisibilityAttributes(), ['class' => 'form-control', 'data-action-change' => 'ui.form.submit']); ?>
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
            [
                'attribute' => 'memberCount',
                'label' => Yii::t('SpaceModule.base', 'Members')
            ],
            ['class' => ImageColumn::class, 'userAttribute' => 'ownerUser'],
            [
                'attribute' => 'ownerUser.profile.lastname',
                'class' => DisplayNameColumn::class,
                'userAttribute' => 'ownerUser',
                'label' => Yii::t('SpaceModule.base', 'Owner')
            ],
            ['class' => SpaceActionColumn::class],
        ],
    ]);
    ?>
</div>
