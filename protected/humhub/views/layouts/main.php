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
        <!-- start: Meta -->
        <meta charset="utf-8">
        <title><?php echo $this->pageTitle; ?></title>
        <!-- end: Meta -->

        <!-- start: Mobile Specific -->
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

        <!-- end: Mobile Specific -->
        <?= Html::csrfMetaTags() ?>
        <?php $this->head() ?>

        <!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->

        <!--[if lt IE 9]>
        <script src="<?php echo Yii::getAlias("@web"); ?>/js/html5shiv.js"></script>
        <link id="ie-style" href="<?php echo Yii::getAlias("@web"); ?>/css/ie.css" rel="stylesheet">
        <![endif]-->
        <!--[if IE 9]>
        <link id="ie9style" href="<?php echo Yii::getAlias("@web"); ?>/css/ie9.css" rel="stylesheet">
        <![endif]-->

        <!-- start: render additional head (css and js files) -->
        <?php echo $this->render('head'); ?>
        <!-- end: render additional head -->
        
        <meta charset="<?= Yii::$app->charset ?>">
    </head>

    <body>
        <?php $this->beginBody() ?>
        <?php echo \humhub\widgets\JSConfig::widget(); ?>

        <!-- start: first top navigation bar -->
        <div id="topbar-first" class="topbar">
            <div class="container">
                <div class="topbar-brand hidden-xs">
                    <?php echo \humhub\widgets\SiteLogo::widget(); ?>
                </div>

                <div class="topbar-actions pull-right">
                    <?php echo \humhub\modules\user\widgets\AccountTopMenu::widget(); ?>
                </div>

                <div class="notifications pull-right">
                    <?php
                    echo \humhub\widgets\NotificationArea::widget(['widgets' => [
                            [\humhub\modules\notification\widgets\Overview::className(), [], ['sortOrder' => 10]],
                    ]]);
                    ?>
                </div>

            </div>

        </div>
        <!-- end: first top navigation bar -->

        <!-- start: second top navigation bar -->
        <div id="topbar-second" class="topbar">
            <div class="container">
                <ul class="nav ">
                    <!-- load space chooser widget -->
                    <?php echo \humhub\modules\space\widgets\Chooser::widget(); ?>

                    <!-- load navigation from widget -->
                    <?php echo \humhub\widgets\TopMenu::widget(); ?>
                </ul>

                <ul class="nav pull-right" id="search-menu-nav">
                    <?php echo \humhub\widgets\TopMenuRightStack::widget(); ?>
                </ul>
            </div>
        </div>
        <!-- end: second top navigation bar -->

        <?= $content; ?>
        <?= \humhub\widgets\LayoutAddons::widget(); ?>

        <?php $this->endBody() ?>

    </body>

</html>
<?php $this->endPage() ?>
