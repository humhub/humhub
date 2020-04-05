<?php

use humhub\libs\LogoImage;
use yii\helpers\Html;

/* @var $place string */
?>
<?php if ($place == "topMenu") : ?>
    <?php if (LogoImage::hasImage()) : ?>
        <a class="navbar-brand hidden-xs" href="<?= Yii::$app->homeUrl; ?>">
            <img class="img-rounded" src="<?= LogoImage::getUrl(); ?>"
                 alt="<?= Yii::t('base', 'Logo of {appName}', ['appName' => Html::encode(Yii::$app->name)]) ?>"
                 id="img-logo"/>
        </a>
    <?php else: ?>
        <a class="navbar-brand navbar-brand-text"
           href="<?= Yii::$app->homeUrl; ?>" id="text-logo">
            <?= Html::encode(Yii::$app->name); ?>
        </a>
    <?php endif; ?>
<?php endif; ?>

<?php if ($place == "login") : ?>
    <?php if (LogoImage::hasImage()) : ?>
        <a href="<?= Yii::$app->homeUrl; ?>" data-pjax-prevent>
            <img class="img-rounded" src="<?= LogoImage::getUrl(500, 250); ?>" id="img-logo"
                 alt="<?= Yii::t('base', 'Logo of {appName}', ['appName' => Html::encode(Yii::$app->name)]) ?>"/>
        </a>
        <br>
    <?php else: ?>
        <h1 id="app-title" class="animated fadeIn"><?= Html::encode(Yii::$app->name); ?></h1>
    <?php endif; ?>
<?php endif; ?>
