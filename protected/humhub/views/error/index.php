<?php

use humhub\helpers\Html;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\FooterMenu;

/* @var \humhub\components\View $this */
/* @var string $message */
/* @var string $buttonHref */
/* @var string $buttonLabel */

$this->pageTitle = Yii::t('base', 'Error');

?>
<div class="container">
    <div class="card content-card">
        <div class="card-header">
            <div class="card-title"><?= Yii::t('base', 'Oooops...'); ?> <?= Yii::t('base', 'It looks like you may have taken the wrong turn.'); ?></div>
        </div>
        <div class="card-body">

            <div class="error">
                <h1 class="h2"><?= Html::encode($message); ?></h1>
            </div>

            <hr>
            <?= Button::primary($buttonLabel)->link($buttonHref, false); ?>
        </div>
    </div>

    <?= FooterMenu::widget(); ?>
</div>
