<?php

use humhub\assets\AppAsset;
use humhub\helpers\Html;
use humhub\modules\user\helpers\LoginBackgroundImageHelper;
use humhub\widgets\FooterMenu;
use yii\web\View;

/* @var $this View */
/* @var $content string */

AppAsset::register($this);

if (LoginBackgroundImageHelper::hasImage()) {
    $this->registerCss(
        '.login-container { background-image: url("' . LoginBackgroundImageHelper::getUrl() . '"); }'
    );
}

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="<?= LoginBackgroundImageHelper::hasImage() ? 'login-layout-background' : '' ?>">
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
<br/>
<?= FooterMenu::widget(['location' => FooterMenu::LOCATION_LOGIN]); ?>
<?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>
