<?php

use humhub\helpers\Html;
use humhub\widgets\SiteLogo;

/**
 * @var $place string
 * @var $maxWidth int
 * @var $maxHeight int
 * @var $id string
 * @var $class string
 */

if (Yii::$app->img->logo->exists()) {
    $alt = Yii::t('base', 'Logo of {appName}', ['appName' => Html::encode(Yii::$app->name)]);
    $size = ['maxWidth' => $maxWidth, 'maxHeight' => $maxHeight];
    $img = Html::img(Yii::$app->img->logo->getUrl($size), ['id' => $id, 'class' => $class, 'alt' => $alt]);

    // Both logos, CSS shows the one of the colour mode (the mode may follow the system setting)
    if (Yii::$app->img->logoDark->exists() && Yii::$app->appearance->isDarkModeAllowed()) {
        $img = Html::img(Yii::$app->img->logo->getUrl($size), ['id' => $id, 'class' => [$class, 'hh-logo-light'], 'alt' => $alt])
            . Html::img(Yii::$app->img->logoDark->getUrl($size), ['class' => [$class, 'hh-logo-dark'], 'alt' => $alt]);
    }
}
?>

<?php if ($place === SiteLogo::PLACE_TOP_MENU) : ?>
    <?php if (Yii::$app->img->logo->exists()) : ?>
        <a class="navbar-brand d-none d-sm-block" href="<?= Yii::$app->homeUrl ?>">
            <?= $img ?>
        </a>
    <?php else: ?>
        <a class="navbar-brand navbar-brand-text"
           href="<?= Yii::$app->homeUrl ?>" id="text-logo">
            <?= Html::encode(Yii::$app->name) ?>
        </a>
    <?php endif; ?>
<?php endif; ?>

<?php if ($place === SiteLogo::PLACE_LOGIN) : ?>
    <?php if (Yii::$app->img->logo->exists()) : ?>
        <a href="<?= Yii::$app->homeUrl ?>" data-pjax-prevent>
            <?= $img ?>
        </a>
        <br>
    <?php else: ?>
        <div id="app-title" class="h1 animated fadeIn"><?= Html::encode(Yii::$app->name) ?></div>
    <?php endif; ?>
<?php endif; ?>
