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
    <div class="panel panel-default">
        <div class="panel-heading">
            <?= Yii::t('base', 'Oooops...'); ?> <?= Yii::t('base', 'It looks like you may have taken the wrong turn.'); ?>
        </div>
        <div class="panel-body">

            <div class="error">
                <h2><?= Html::encode($message); ?></h2>
            </div>

            <hr>
            <?= Button::primary($buttonLabel)->link($buttonHref); ?>
        </div>
    </div>

    <?= FooterMenu::widget(); ?>
</div>
