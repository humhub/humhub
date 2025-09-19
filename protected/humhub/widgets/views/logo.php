<?php

use humhub\helpers\Html;
use humhub\libs\LogoImage;
use humhub\widgets\SiteLogo;

/**
 * @var $place string
 * @var $maxWidth int
 * @var $maxHeight int
 * @var $id string
 * @var $class string
 */

$hasLogoImage = LogoImage::hasImage();
if ($hasLogoImage) {
    $img = Html::img(LogoImage::getUrl($maxWidth, $maxHeight), [
        'id' => $id,
        'class' => $class,
        'alt' => Yii::t('base', 'Logo of {appName}', ['appName' => Html::encode(Yii::$app->name)]),
    ]);
}
?>

<?php if ($place === SiteLogo::PLACE_TOP_MENU) : ?>
    <?php if ($hasLogoImage) : ?>
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
    <?php if ($hasLogoImage) : ?>
        <a href="<?= Yii::$app->homeUrl ?>" data-pjax-prevent>
            <?= $img ?>
        </a>
        <br>
    <?php else: ?>
        <h1 id="app-title" class="animated fadeIn"><?= Html::encode(Yii::$app->name) ?></h1>
    <?php endif; ?>
<?php endif; ?>
