<?php

use humhub\helpers\Html;
use humhub\modules\user\authclient\BaseFormAuth;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\bootstrap\Link;

/* @var BaseFormAuth[] $authClients */
/* @var string $currentAuthProviderId */
/* @var string[] $activeAuthClientIds */
?>

<?php $this->beginContent('@user/views/account/_userSettingsLayout.php') ?>
<div class="text-body-secondary">
    <?= Yii::t('UserModule.base', 'Here you can connect to external service provider for using external services like a single sign on authentication.') ?>
</div>
<table class="table table-hover" aria-label="<?= Html::encode(Yii::t('UserModule.base', 'Connected accounts')) ?>">
    <caption class="visually-hidden"><?= Yii::t('UserModule.base', 'Connected accounts') ?></caption>
    <thead>
        <tr>
            <th scope="col" colspan="2"><?= Yii::t('UserModule.base', 'Provider') ?></th>
            <th scope="col" class="text-center" style="width:50px"><?= Yii::t('UserModule.base', 'Action') ?></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($authClients as $client) : ?>
        <tr>
            <td aria-hidden="true" style="width:10px">
                <div class="<?= $client->getViewOptions()['cssIcon'] ?? '' ?> float-start" style="font-size:200%"></div>
            </td>
            <td class="align-middle">
                <strong><?= $client->getTitle() ?></strong>
            </td>
            <td class="text-center">
                <?php if ($client->getId() == $currentAuthProviderId): ?>
                    <?= Button::light(Yii::t('UserModule.base', 'Currently in use'))
                        ->disabled()
                        ->sm() ?>
                <?php elseif (in_array($client->getId(), $activeAuthClientIds)) : ?>
                    <?= Link::danger(Yii::t('UserModule.base', 'Disconnect account'))
                        ->post(['connected-accounts', 'disconnect' => $client->getId()])
                        ->confirm()
                        ->options(['aria-label' => Yii::t('UserModule.base', 'Disconnect {provider} account', ['provider' => $client->getTitle()])])
                        ->sm() ?>
                <?php else: ?>
                    <?= Button::success(Yii::t('UserModule.base', 'Connect account'))
                        ->link(['/user/auth/external', 'authclient' => $client->getId()])
                        ->options(['aria-label' => Yii::t('UserModule.base', 'Connect {provider} account', ['provider' => $client->getTitle()])])
                        ->sm()
                        ->pjax(false) ?>
                <?php endif ?>
            </td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>
<?php $this->endContent() ?>
