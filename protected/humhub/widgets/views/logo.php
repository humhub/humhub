<?php

use humhub\helpers\Html;
use humhub\libs\LogoImage;
use humhub\widgets\SiteLogo;
use yii\helpers\Url;

/**
 * @var $place string
 * @var $maxWidth int
 * @var $maxHeight int
 * @var $id string
 * @var $class string
 * @var $style string
 */

$hasLogoImage = LogoImage::hasImage();
if ($hasLogoImage) {
    $imgUrl = LogoImage::getUrl($maxWidth, $maxHeight);
    if ($place === SiteLogo::PLACE_EMAIL) {
        // Use absolute URL for email logo
        $imgUrl = Url::to($imgUrl, true);
    }
    $img = Html::img($imgUrl, [
        'id' => $id,
        'class' => $class,
        'style' => $style,
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

<?php if ($place === SiteLogo::PLACE_EMAIL) : ?>
    <?php if ($hasLogoImage) : ?>
        <a href="<?= Url::to(['/'], true) ?>">
            <?= $img ?>
        </a>
    <?php endif; ?>
<?php endif; ?>
