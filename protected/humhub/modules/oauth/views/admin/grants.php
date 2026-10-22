<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\oauth\models\Client;
use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\models\GrantSearch;
use humhub\widgets\bootstrap\Badge;
use humhub\widgets\bootstrap\Link;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/* @var View $this */
/* @var GrantSearch $searchModel */
/* @var ActiveDataProvider $dataProvider */

$clients = ArrayHelper::map(Client::find()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name');
$filter = array_filter($searchModel->getAttributes(), fn($value) => $value !== null && $value !== '');
$never = Yii::t('OauthModule.base', 'Never');
?>
<?php $this->beginContent('@admin/views/authentication/_authenticationLayout.php') ?>
<?= $this->render('_tabs') ?>
<div class="panel-body">
    <h4><?= Yii::t('OauthModule.base', 'Grants') ?></h4>
    <p class="text-body-secondary">
        <?= Yii::t('OauthModule.base', 'Connected apps, devices and personal access tokens of all users.') ?>
    </p>

    <?php $form = ActiveForm::begin(['method' => 'get', 'action' => ['/oauth/admin/grants'], 'id' => 'oauth-grant-filter']) ?>
    <div class="row gy-2 mb-3">
        <div class="col-lg-4">
            <?= Html::activeTextInput($searchModel, 'user', ['class' => 'form-control', 'placeholder' => Yii::t('OauthModule.base', 'User (name, username or email)')]) ?>
        </div>
        <div class="col-lg-3">
            <?= Html::activeDropDownList($searchModel, 'client_id', $clients, ['class' => 'form-select', 'prompt' => Yii::t('OauthModule.base', 'All clients')]) ?>
        </div>
        <div class="col-lg-2">
            <?= Html::activeDropDownList($searchModel, 'type', [
                Grant::TYPE_OAUTH => Yii::t('OauthModule.base', 'Connected app'),
                Grant::TYPE_PAT => Yii::t('OauthModule.base', 'Personal access token'),
            ], ['class' => 'form-select', 'prompt' => Yii::t('OauthModule.base', 'All types')]) ?>
        </div>
        <div class="col-lg-3">
            <?= Html::submitButton(Yii::t('OauthModule.base', 'Filter'), ['class' => 'btn btn-primary']) ?>
            <?php if ($filter !== []): ?>
            <?= Link::danger(Yii::t('OauthModule.base', 'Revoke all'))
                ->post(['/oauth/admin/revoke-grants', $searchModel->formName() => $filter])
                ->confirm(
                    Yii::t('OauthModule.base', 'Revoke all matching grants?'),
                    Yii::t('OauthModule.base', 'All {count} grants matching the current filter are revoked.', [
                        'count' => $dataProvider->getTotalCount(),
                    ]),
                ) ?>
            <?php endif ?>
        </div>
    </div>
    <?php ActiveForm::end() ?>

    <div class="table-responsive">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'summary' => '',
            'tableOptions' => ['class' => 'table table-hover'],
            'columns' => [
                [
                    'label' => Yii::t('OauthModule.base', 'User'),
                    'format' => 'raw',
                    'value' => fn(Grant $grant) => $grant->user === null ? '' : Html::a(
                        Html::encode($grant->user->displayName),
                        Url::to(['/admin/user/edit', 'id' => $grant->user_id]),
                    ),
                ],
                [
                    'label' => Yii::t('OauthModule.base', 'Type'),
                    'value' => fn(Grant $grant) => $grant->type === Grant::TYPE_PAT
                        ? Yii::t('OauthModule.base', 'Personal access token')
                        : Yii::t('OauthModule.base', 'Connected app'),
                ],
                [
                    'label' => Yii::t('OauthModule.base', 'Name / client'),
                    'format' => 'raw',
                    'value' => fn(Grant $grant) => Html::encode($grant->client?->name ?? $grant->name),
                ],
                [
                    'label' => Yii::t('OauthModule.base', 'Scopes'),
                    'format' => 'raw',
                    'value' => fn(Grant $grant) => implode(' ', array_map(
                        fn(string $id) => Badge::light($id),
                        $grant->getScopeIds(),
                    )),
                ],
                ['attribute' => 'created_at', 'format' => 'datetime'],
                [
                    'attribute' => 'last_used_at',
                    'value' => fn(Grant $grant) => $grant->last_used_at === null ? $never : Yii::$app->formatter->asDatetime($grant->last_used_at),
                ],
                [
                    'attribute' => 'expires_at',
                    'value' => fn(Grant $grant) => $grant->expires_at === null ? $never : Yii::$app->formatter->asDatetime($grant->expires_at),
                ],
                [
                    'format' => 'raw',
                    'contentOptions' => ['class' => 'text-end text-nowrap'],
                    'value' => fn(Grant $grant) => Link::danger(Yii::t('OauthModule.base', 'Revoke'))
                        ->post(['/oauth/admin/revoke-grant', 'id' => $grant->id, $searchModel->formName() => $filter])
                        ->confirm(Yii::t('OauthModule.base', 'Revoke this grant?'))
                        ->sm(),
                ],
            ],
        ]) ?>
    </div>
</div>
<?php $this->endContent() ?>
