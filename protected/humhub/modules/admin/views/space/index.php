<?php

use humhub\helpers\Html;
use humhub\modules\admin\grid\SpaceActionColumn;
use humhub\modules\admin\grid\SpaceImageColumn;
use humhub\modules\admin\grid\SpaceTitleColumn;
use humhub\modules\admin\models\SpaceSearch;
use humhub\modules\admin\widgets\AdminSpaceCardActions;
use humhub\modules\admin\widgets\SpaceGridView;
use humhub\modules\user\grid\DisplayNameColumn;
use humhub\modules\user\grid\ImageColumn;
use humhub\widgets\ContentCard;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\Icon;
use yii\helpers\Url;

/* @var $searchModel SpaceSearch */
?>

<?php ContentCard::begin([
    'title' => Yii::t('AdminModule.space', 'Overview'),
    'actions' => AdminSpaceCardActions::class,
]) ?>
<?php $form = ActiveForm::begin(['method' => 'get', 'action' => Url::to(['/admin/space'])]); ?>

<div class="container gx-0 overflow-x-hidden">
    <div class="row gy-2">
        <div class="col-lg-8">
            <div class="input-group">
                <?= Html::activeTextInput($searchModel, 'freeText', ['class' => 'form-control', 'placeholder' => Yii::t('AdminModule.space', 'Search by name, description, id or owner.')]); ?>
                <button class="btn btn-light" type="submit"><?= Icon::get('search') ?></button>
            </div>
        </div>
        <div class="col-lg-4 spacesearch-visibilities">
            <?= Html::activeDropDownList($searchModel, 'visibility', SpaceSearch::getVisibilityAttributes(), ['class' => 'form-control', 'data-action-change' => 'ui.form.submit']); ?>
        </div>
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
<?php ContentCard::end() ?>
