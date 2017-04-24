<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use yii\helpers\Html;
?>
<?php if ($place == "topMenu") : ?>
    <?php if ($logo->hasImage()) : ?>
        <a class="navbar-brand hidden-xs" style="height: 50px; padding:5px;"
           href="<?= Yii::$app->homeUrl; ?>">
            <img class="img-rounded" src="<?= $logo->getUrl(); ?>" id="img-logo">
        </a>
    <?php endif; ?>
    <a class="navbar-brand" style="<?php if ($logo->hasImage()) : ?>display:none;<?php endif; ?> "
       href="<?= Yii::$app->homeUrl; ?>" id="text-logo">
           <?= Html::encode(Yii::$app->name); ?>
    </a>
<?php endif; ?>

<?php if ($place == "login") : ?>
    <?php if ($logo->hasImage()) : ?>
        <a href="<?= Yii::$app->homeUrl; ?>">
            <img class="img-rounded" src="<?= $logo->getUrl(); ?>" id="img-logo">
        </a>
        <br>
    <?php else: ?>
        <h1 id="app-title" class="animated fadeIn"><?= Html::encode(Yii::$app->name); ?></h1>
    <?php endif; ?>
<?php endif; ?>
