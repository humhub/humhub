<?php
/* @var $this \yii\web\View */
/* @var $content string */

\humhub\assets\AppAsset::register($this);

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url; ?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <title><?= strip_tags($this->pageTitle); ?></title>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <?php $this->head() ?>
        <?= $this->render('head'); ?>
    </head>
    <body>
        <?php $this->beginBody() ?>

        <!-- start: first top navigation bar -->
        <div id="topbar-first" class="topbar">
            <div class="container">

                <div class="topbar-menu visible-xs-inline-block visible-sm-inline-block pull-left">
                    <?= \humhub\widgets\TopMenu::widget(); ?>
                </div>

                <div class="topbar-brand">
                    <?= \humhub\widgets\SiteLogo::widget(); ?>
                </div>

                <div class="topbar-coins pull-right">
                    <?= \humhub\modules\xcoin\widgets\AssetAmount::widget() ?>
                </div>

                <div class="topbar-actions pull-right">
                    <?= \humhub\modules\user\widgets\AccountTopMenu::widget(); ?>
                </div>

                <div class="notifications pull-right hidden-xs">
                    <?= \humhub\widgets\NotificationArea::widget(); ?>
                </div>

                <div class="spaces pull-right hidden-xs">
                    <!-- load space chooser widget -->
                    <?= \humhub\modules\space\widgets\Chooser::widget(); ?>
                </div>

                <div class="search pull-right hidden-xs">
                    <?= \yii\helpers\Html::a(
                        '<i class="fa fa-search"></i>',
                        ['/search']) ?>
                </div>

            </div>
        </div>
        <!-- end: first top navigation bar -->

        <!-- start: second top navigation bar -->
        <div id="topbar-second" class="topbar visible-md visible-lg">
            <div class="container">
                <ul class="nav" id="top-menu-nav">
                    <!-- load navigation from widget -->
                    <?= \humhub\widgets\TopMenu::widget(); ?>
                </ul>
            </div>
        </div>
        <!-- end: second top navigation bar -->
        <div id="bottombar" class="bottombar visible-xs">
            <div class="container links">
                <?= \yii\helpers\Html::a(
                    '<i class="fa fa-home"></i>',
                    ['/dashboard/dashboard'],
                    Yii::$app->requestedRoute == "dashboard/dashboard" ? [ 'class' => ['active', 'home'] ] : ['class' => 'home']); ?>
                <?= \yii\helpers\Html::a(
                    '<i class="fa fa-bell"></i>',
                    ['/notification/overview'],
                    Yii::$app->requestedRoute == "notification/overview" ? [ 'class' => ['active', 'notifications'] ] : ['class' => 'notifications']); ?>
                <?= \yii\helpers\Html::a(
                    '<i class="fa fa-envelope"></i>',
                    ['/mail/mail/index'],
                    Yii::$app->requestedRoute == "mail/mail/index" ? ['class' => ['active', 'messages'] ] : ['class' => 'messages']); ?>

                <?= \yii\helpers\Html::a(
                    '<i class="fa fa-dot-circle-o"></i>',
                    ['/directory/spaces'],
                    Yii::$app->requestedRoute == "directory/spaces" ? [ 'class' => ['active', 'spaces'] ] : ['class' => 'spaces']); ?>


                <?= \yii\helpers\Html::a(
                    '<i class="fa fa-search"></i>',
                    ['/search/search/index'],
                    Yii::$app->requestedRoute == "search/search/index" ? [ 'class' => ['active', 'search'] ] : ['class' => 'search']); ?>
            </div>

        </div>

        <script>
            $('body').off('click', '#bottombar .links a');
            $('body').on('click', '#bottombar .links a' ,function () {
                $('#bottombar .links a').removeClass('active');
                setTimeout(function () {
                    switch (window.location.pathname) {
                        case '/dashboard':
                            $('#bottombar .links .home').removeClass('active').addClass('active');
                            break;
                        case '/notification/overview':
                            $('#bottombar .links .notifications').removeClass('active').addClass('active');
                            break;
                        case '/mail/mail/index':
                            $('#bottombar .links .messages').removeClass('active').addClass('active');
                            break;
                        case '/directory/spaces':
                            $('#bottombar .links .spaces').removeClass('active').addClass('active');
                            break;
                        case '/search':
                            $('#bottombar .links .search').removeClass('active').addClass('active');
                            break;
                        default:
                            break;
                    }
                })
            });
        </script>

        <?= $content; ?>

        <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>
