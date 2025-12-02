<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\helpers\ScssHelper;
use humhub\modules\ui\mail\DefaultMailStyle;
use yii\helpers\Url;

/**
 * @var $this View
 * @var $imgUrl string|null
 * @var $appName string
 * @var $verticalMargin int
 * @var $backgroundColor ?string
 */

$contrastColor = $backgroundColor
    ? ScssHelper::getColorContrast($backgroundColor)
    : $this->theme->variable('text-color-contrast', '#ffffff');
?>

<a href="<?= Url::to(['/'], true) ?>"
   style="text-decoration: none; font-size: 18px; font-family: <?= $this->theme->variable('mail-font-family', DefaultMailStyle::DEFAULT_FONT_FAMILY) ?>; color: <?= $contrastColor ?>; font-weight: 700;">
    <?php if ($imgUrl) : ?>
        <?= Html::img($imgUrl, ['alt' => $appName, 'style' => 'margin:' . $verticalMargin . 'px auto;']) ?>
    <?php else: ?>
        <span style="display: inline-block; line-height: 27px; text-align: left; margin: <?= $verticalMargin ?>px 0;">
            <?= Html::encode($appName) ?>
        </span>
    <?php endif; ?>
</a>
