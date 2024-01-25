<?php

use humhub\widgets\FooterMenu;
use yii\helpers\Html;
use yii\helpers\Url;

$this->pageTitle = Yii::t('base', 'Error');
?>
<div class="container">
    <div class="card card-default">
        <div class="card-header">
            <?= Yii::t('base', 'Oooops...'); ?> <?= Yii::t('base', 'It looks like you may have taken the wrong turn.'); ?>
        </div>
        <div class="card-body">

            <div class="error">
                <h2><?= Html::encode($message); ?></h2>
            </div>

            <hr>
            <a href="<?= Url::home() ?>" class="btn btn-primary"><?= Yii::t('base', 'Back to dashboard'); ?></a>
        </div>
    </div>

    <?= FooterMenu::widget(); ?>
</div>
