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
    $img = Html::img(Yii::$app->img->logo->getUrl(['maxWidth' => $maxWidth, 'maxHeight' => $maxHeight]), [
        'id' => $id,
        'class' => $class,
        'alt' => Yii::t('base', 'Logo of {appName}', ['appName' => Html::encode(Yii::$app->name)]),
    ]);
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
        <h1 id="app-title" class="animated fadeIn"><?= Html::encode(Yii::$app->name) ?></h1>
    <?php endif; ?>
<?php endif; ?>
