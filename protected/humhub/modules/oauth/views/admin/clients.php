<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\libs\ActionColumn;
use humhub\modules\oauth\models\Client;
use humhub\widgets\bootstrap\Badge;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\bootstrap\Link;
use humhub\widgets\GridView;
use yii\data\ActiveDataProvider;

/* @var View $this */
/* @var ActiveDataProvider $dataProvider */

$types = [
    Client::TYPE_SYSTEM => Yii::t('OauthModule.base', 'HumHub app'),
    Client::TYPE_ADMIN => Yii::t('OauthModule.base', 'Administrator'),
    Client::TYPE_USER => Yii::t('OauthModule.base', 'User'),
];
?>
<?php $this->beginContent('@admin/views/authentication/_authenticationLayout.php') ?>
<?= $this->render('_tabs') ?>
<div class="panel-body">
    <?= Button::success(Yii::t('OauthModule.base', 'Add client'))->icon('add')->sm()->link(['/oauth/admin/client'])->right() ?>
    <h4><?= Yii::t('OauthModule.base', 'Clients') ?></h4>
    <p class="text-body-secondary">
        <?= Yii::t('OauthModule.base', 'Applications that may ask users for access. Deactivating or deleting a client ends the access of all its grants.') ?>
    </p>

    <div class="table-responsive">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'summary' => '',
            'tableOptions' => ['class' => 'table table-hover'],
            'columns' => [
                ['attribute' => 'name'],
                [
                    'attribute' => 'type',
                    'value' => fn(Client $client) => $types[$client->type] ?? $client->type,
                ],
                [
                    'attribute' => 'client_id',
                    'format' => 'raw',
                    'value' => fn(Client $client) => Html::tag('span', Html::encode($client->client_id), ['class' => 'font-monospace']),
                ],
                [
                    'attribute' => 'is_active',
                    'label' => Yii::t('OauthModule.base', 'Active'),
                    'format' => 'raw',
                    'value' => fn(Client $client) => $client->is_active
                        ? Badge::success(Yii::t('OauthModule.base', 'Active'))
                        : Badge::light(Yii::t('OauthModule.base', 'Inactive')),
                ],
                ['attribute' => 'created_at', 'format' => 'datetime'],
                [
                    'class' => ActionColumn::class,
                    'actions' => fn(Client $client) => $client->type === Client::TYPE_SYSTEM ? [] : [
                        Yii::t('OauthModule.base', 'Edit') => ['client', 'id' => $client->id],
                        '---',
                        Yii::t('OauthModule.base', 'Delete') => [
                            'delete-client', 'id' => $client->id,
                            'linkOptions' => [
                                'data-method' => 'post',
                                'data-confirm' => Yii::t('OauthModule.base', 'Delete this client? All its grants are revoked.'),
                            ],
                        ],
                    ],
                ],
            ],
        ]) ?>
    </div>
</div>
<?php $this->endContent() ?>
