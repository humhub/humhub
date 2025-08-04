<?php

use humhub\helpers\Html;
use humhub\modules\admin\assets\AdminPendingRegistrationsAsset;
use humhub\modules\admin\models\PendingRegistrationSearch;
use humhub\modules\admin\widgets\ExportButton;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\GridView;
use yii\helpers\Url;

/** @var $searchModel PendingRegistrationSearch */
/** @var $dataProvider yii\data\ActiveDataProvider */
/** @var $types array */

AdminPendingRegistrationsAsset::register($this);
?>
<?= Html::beginTag('div', $options); ?>
    <h4>
        <?= Html::backButton(
            ['/admin/user/index'],
            [
                'label' => Yii::t('AdminModule.base', 'Back to user overview'),
                'class' => 'btn-sm float-end',
            ],
        ) ?>
        <?= Yii::t('AdminModule.base', 'Pending user registrations') ?>
    </h4>

    <div class="text-body-secondary">
        <?= Yii::t('AdminModule.user', 'The following list contains all pending sign-ups and invites.') ?>
    </div>

    <div class="float-end">
        <?php if ($dataProvider->totalCount > 0): ?>
            <?= Button::primary(Yii::t('AdminModule.user', 'Re-send to all'))
                ->icon('paper-plane')
                ->action('resendAll', Url::toRoute(['/admin/pending-registrations/resend-all']))
                ->cssClass('resend-all btn-sm')->
                confirm(Yii::t('AdminModule.user', 'Resend invitations?'), Yii::t('AdminModule.user', 'Do you really want to re-send the invitations to pending registration users?')) ?>
            <?= Button::danger(Yii::t('AdminModule.user', 'Delete All'))
                ->icon('trash')
                ->action('deleteAll', Url::toRoute(['/admin/pending-registrations/delete-all']))
                ->cssClass('delete-all btn-sm')->
                confirm(Yii::t('AdminModule.user', 'Delete pending registrations?'), Yii::t('AdminModule.user', 'Do you really want to delete pending registrations?')) ?>
        <?php endif; ?>
        <?= ExportButton::widget(['filter' => 'PendingRegistrationSearch']) ?>
    </div>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'id' => 'grid',
    'columns' => [
        [
            'class' => 'yii\grid\CheckboxColumn',
            'cssClass' => 'select-on-check-one',
            'checkboxOptions' => fn($data) => ['id' => $data->id],
            'contentOptions' => ['style' => 'width:auto; white-space: normal;'],
        ],
        [
            'attribute' => 'email',
            'format' => 'email',
        ],
        'originator.username',
        [
            'attribute' => 'language',
            'contentOptions' => ['style' => 'width:80px; white-space: normal;'],
        ],

        'created_at',
        [
            'attribute' => 'source',
            'filter' => Html::activeDropDownList($searchModel, 'source', $types),
            'options' => ['width' => '40px'],
            'format' => 'raw',
            'value' => fn($data) => $types[$data->source] ?? Html::encode($data->source),
        ],
        [
            'header' => Yii::t('AdminModule.user', 'Actions'),
            'class' => 'yii\grid\ActionColumn',
            'template' => '{resend} {delete}',
            'buttons' => [
                'resend' => fn($url, $model, $key) => Button::primary()
                    ->action('client.post', Url::to(['resend', 'id' => $model->id]))
                    ->icon('paper-plane')
                    ->confirm(Yii::t('AdminModule.user', 'Resend invitation?'))
                    ->sm(),
                'delete' => fn($url, $model, $key) => Button::danger()
                    ->action('client.post', Url::to(['delete', 'id' => $model->id]))
                    ->icon('trash')
                    ->confirm(Yii::t('AdminModule.user', 'Delete pending registrations?'))
                    ->sm(),
            ],
        ],

    ],
]) ?>
<?= Html::endTag('div');
