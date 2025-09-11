<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\ui\mail\DefaultMailStyle;
use yii\helpers\Url;

/**
 * @var $this View
 * @var $imgUrl string|null
 * @var $appName string
 */
?>

<a href="<?= Url::to(['/'], true) ?>"
   style="text-decoration: none; font-size: 18px; font-family: <?= $this->theme->variable('mail-font-family', DefaultMailStyle::DEFAULT_FONT_FAMILY) ?>; color: <?= $this->theme->variable('text-color-contrast', '#ffffff') ?>; font-weight: 700;">
    <?php if ($imgUrl) : ?>
        <?= Html::img($imgUrl, ['alt' => $appName, 'style' => 'margin:10px auto;']) ?>
    <?php else: ?>
        <span style="display: inline-block; line-height: 27px; text-align: left; margin: 10px 0;">
            <?= Html::encode($appName) ?>
        </span>
    <?php endif; ?>
</a>
