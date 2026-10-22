<?php

use humhub\helpers\Html;
use humhub\modules\oauth\models\Client;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\bootstrap\Link;
use humhub\widgets\Icon;

/* @var Client[] $clients */
?>
<?php if ($clients !== []): ?>
    <ul class="list-group mb-3">
        <?php foreach ($clients as $client): ?>
            <li class="list-group-item d-flex align-items-center gap-3" data-client-id="<?= $client->id ?>">
                <?= Icon::get('code')->size(Icon::SIZE_LG) ?>
                <div class="flex-grow-1">
                    <strong><?= Html::encode($client->name) ?></strong>
                    <div class="text-body-secondary small">
                        <span class="font-monospace"><?= Html::encode($client->client_id) ?></span>
                        · <?= $client->isConfidential()
                            ? Yii::t('OauthModule.base', 'Confidential')
                            : Yii::t('OauthModule.base', 'Public') ?>
                        <?php if (!$client->is_active): ?>
                            · <span class="text-danger"><?= Yii::t('OauthModule.base', 'Deactivated') ?></span>
                        <?php endif ?>
                    </div>
                </div>
                <?= Link::light(Yii::t('OauthModule.base', 'Edit'))
                    ->link(['/oauth/account/client', 'id' => $client->id])
                    ->sm() ?>
                <?= Link::danger(Yii::t('OauthModule.base', 'Delete'))
                    ->post(['/oauth/account/delete-client', 'id' => $client->id])
                    ->confirm(
                        Yii::t('OauthModule.base', 'Delete this app?'),
                        Html::encode(Yii::t('OauthModule.base', 'The app loses access to your account and cannot be connected again.')),
                    )
                    ->sm() ?>
            </li>
        <?php endforeach ?>
    </ul>
<?php endif ?>

<?= Button::primary(Yii::t('OauthModule.base', 'Register app'))
    ->link(['/oauth/account/client'])
    ->icon('plus')
    ->sm() ?>
