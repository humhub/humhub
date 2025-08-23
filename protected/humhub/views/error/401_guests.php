<?php

use humhub\helpers\Html;
use humhub\widgets\FooterMenu;
use yii\helpers\Url;

?>
<div class="container">
    <div class="panel panel-danger">
        <div class="panel-heading">
            <?= Yii::t('error', "<strong>Login</strong> required"); ?>
        </div>
        <div class="panel-body">

            <strong><?= Html::encode($message); ?></strong>

            <br>
            <hr>
            <button class="btn btn-accent" data-action-click="ui.modal.load"
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
