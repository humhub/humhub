<?php

use humhub\assets\AppAsset;
use humhub\components\View;
use humhub\helpers\DeviceDetectorHelper;
use humhub\helpers\Html;
use humhub\modules\space\widgets\Chooser;
use humhub\modules\user\widgets\AccountTopMenu;
use humhub\widgets\NotificationArea;
use humhub\widgets\SiteLogo;
use humhub\widgets\TopMenu;
use humhub\widgets\TopMenuRightStack;

/* @var $this View */
/* @var $content string */

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <title><?= strip_tags((string) $this->pageTitle) ?></title>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
        <?php $this->head() ?>
        <?= $this->render('head') ?>
    </head>

    <?= Html::beginTag('body', ['class' => DeviceDetectorHelper::getBodyClasses()]) ?>
        <?php $this->beginBody() ?>

        <!-- start: first top navigation bar -->
        <div id="topbar-first" class="topbar navbar">
            <div class="container d-flex justify-content-between">
                <div class="topbar-brand d-none d-md-block">
                    <?= SiteLogo::widget() ?>
                </div>

                <div class="notifications">
                    <?= NotificationArea::widget() ?>
                </div>

                <div class="topbar-actions">
                    <?= AccountTopMenu::widget() ?>
                </div>
            </div>
        </div>
        <!-- end: first top navigation bar -->

        <!-- start: second top navigation bar -->
        <div id="topbar-second" class="topbar navbar">
            <div class="container">
                <ul class="nav" id="top-menu-nav">
                    <!-- load space chooser widget -->
                    <?= Chooser::widget() ?>

                    <!-- load navigation from widget -->
                    <?= TopMenu::widget() ?>
                </ul>

                <ul class="nav float-end" id="search-menu-nav">
                    <?= TopMenuRightStack::widget() ?>
                </ul>
            </div>
        </div>
        <!-- end: second top navigation bar -->

        <?= $content ?>

        <?php $this->endBody() ?>
    <?= Html::endTag('body') ?>
</html>
<?php $this->endPage() ?>
