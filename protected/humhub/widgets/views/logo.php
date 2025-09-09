<?php

use humhub\helpers\Html;
use humhub\libs\LogoImage;
use humhub\modules\ui\mail\DefaultMailStyle;
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
    <a href="<?= Url::to(['/'], true) ?>"
       style="text-decoration: none; font-size: 18px; font-family: <?= $this->theme->variable('mail-font-family', DefaultMailStyle::DEFAULT_FONT_FAMILY) ?>; color: <?= $this->theme->variable('text-color-contrast', '#ffffff') ?>; font-weight: 700;">
        <?php if ($hasLogoImage) : ?>
            <?= $img ?>
        <?php else: ?>
            <span style="display: inline-block; line-height: 27px; text-align: left; margin: 10px 0;">
                <?= Html::encode(Yii::$app->name) ?>
            </span>
        <?php endif; ?>
    </a>
<?php endif; ?>
