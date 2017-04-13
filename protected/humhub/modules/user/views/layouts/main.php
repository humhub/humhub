<?php

use yii\helpers\Html;
use humhub\assets\AppAsset;

/* @var $this \yii\web\View */
/* @var $content string */

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?= Html::encode($this->pageTitle); ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <?php $this->head() ?>
        <?= $this->render('@humhub/views/layouts/head'); ?>
        <meta charset="<?= Yii::$app->charset ?>">
    </head>

    <body class="login-container">
        <?php $this->beginBody() ?>
        <?= $content; ?>
        <?php $this->endBody() ?>

        <div class="text text-center powered">Powered by <a href="http://www.humhub.org" target="_blank">HumHub</a></div>
    </body>

</html>
<?php $this->endPage() ?>
