<?php

use humhub\modules\admin\grid\UserActionColumn;
use humhub\modules\admin\models\UserSearch;
use humhub\modules\admin\widgets\ExportButton;
use humhub\modules\user\grid\DisplayNameColumn;
use humhub\modules\user\grid\ImageColumn;
use humhub\widgets\Button;
use humhub\widgets\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/**
 * @var $searchModel UserSearch
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $showPendingRegistrations bool
 */
?>

<div class="panel-body">

    <div class="pull-right">
        <?= Button::success(Yii::t('AdminModule.user', 'Add new user'))->icon('add')->sm()->link(['/admin/user/add']) ?>
        <?= ExportButton::widget(['filter' => 'UserSearch']) ?>
    </div>

    <h4><?= Yii::t('AdminModule.user', 'Overview'); ?></h4>
    <div class="help-block">
        <?= Yii::t('AdminModule.user', 'This overview contains a list of each registered user with actions to view, edit and delete users.'); ?>
    </div>

    <br />

    <?php $form = ActiveForm::begin(['method' => 'get', 'action' => Url::to(['/admin/user/list'])]); ?>
    <div class="row">
        <div class="col-md-8">
            <div class="input-group">
                <?= Html::activeTextInput($searchModel, 'freeText', ['class' => 'form-control', 'placeholder' => Yii::t('AdminModule.user', 'Search by name, email or id.')]); ?>
                <span class="input-group-btn">
                    <button class="btn btn-default" type="submit"><i class="fa fa-search"></i></button>
                </span>
            </div>
        </div>
        <div class="col-md-4 usersearch-statuses">
            <?= Html::activeDropDownList($searchModel, 'status', UserSearch::getStatusAttributes(), ['class' => 'form-control', 'data-action-change' => 'ui.form.submit']); ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

    <div class="table-responsive">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'summary' => '',
            'columns' => [
                ['class' => ImageColumn::class],
                ['class' => DisplayNameColumn::class],
                'email',
                [
                    'attribute' => 'last_login',
                    'label' => Yii::t('AdminModule.user', 'Last login'),
                    'options' => ['style' => 'width:120px;'],
                    'value' => function ($data) {
                        return ($data->last_login == null) ? Yii::t('AdminModule.user', 'never') : Yii::$app->formatter->asDate($data->last_login);
                    }
                ],
                ['class' => UserActionColumn::class],
            ],
        ]); ?>
    </div>
    <?php if ($showPendingRegistrations): ?>
        <br/>
        <?= Button::defaultType(Yii::t('AdminModule.user', 'List pending registrations'))->link(Url::to(['/admin/pending-registrations']))->right()->sm(); ?>
    <?php endif; ?>
</div>
