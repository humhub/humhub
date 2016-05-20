<?php

use \yii\bootstrap\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use \humhub\models\Setting;
?>

<div class="panel-heading">
    <?php echo Yii::t('UserModule.base', '<strong>Connected</strong> accounts'); ?>
</div>
<div class="panel-body">
    <?= humhub\modules\user\widgets\AccountSettingsMenu::widget(); ?>
    <br />
    <p />

    <?php foreach ($authClients as $client) : ?>
        <div class="media">
            <div class="media-body">
                <h4 class="media-heading"><strong><?php echo $client->getTitle(); ?></strong></h4>
                        <?php if ($client->getId() == $currentAuthProviderId): ?>
                            <?php echo Html::a(Yii::t('UserModule.base', 'Current account'), '#', ['class' => 'btn btn-default pull-right', 'data-method' => 'POST', 'disabled' => 'disabled']); ?>
                        <?php elseif (in_array($client->getId(), $activeAuthClientIds)) : ?>
                            <?php echo Html::a(Yii::t('UserModule.base', 'Disconnect account'), ['connected-accounts', 'disconnect' => $client->getId()], ['class' => 'btn btn-danger pull-right', 'data-method' => 'POST']); ?>
                        <?php else: ?>
                            <?php echo Html::a(Yii::t('UserModule.base', 'Connect account'), Url::to(['/user/auth/external', 'authclient' => $client->getId()]), ['class' => 'btn btn-success pull-right']); ?>
                        <?php endif; ?>

            </div>
        </div>

    <?php endforeach; ?>


</div>
