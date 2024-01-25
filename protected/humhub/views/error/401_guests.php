<?php

use humhub\widgets\FooterMenu;
use yii\helpers\Html;
use yii\helpers\Url;

?>
<div class="container">
    <div class="card card-danger">
        <div class="card-header">
            <?= Yii::t('error', "<strong>Login</strong> required"); ?>
        </div>
        <div class="card-body">

            <strong><?= Html::encode($message); ?></strong>

            <br>
            <hr>
            <button class="btn btn-info" data-action-click="ui.modal.load"
                    data-action-url="<?= Url::to(Yii::$app->user->loginUrl) ?>" data-ui-loader>
                <?= Yii::t('base', 'Login') ?>
            </button>
            <button data-action-click="client.back" class="btn btn-primary float-end" data-ui-loader>
                <?= Yii::t('base', 'Back'); ?>
            </button>
        </div>
    </div>

    <?= FooterMenu::widget(); ?>

</div>
