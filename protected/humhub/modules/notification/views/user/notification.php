<?php

use humhub\components\View;
use humhub\modules\notification\widgets\SettingsPage;
use humhub\modules\notification\widgets\UserInfoWidget;

/* @var $this View */

// The settings island brings its own cards
$this->params['accountPanel'] = false;
?>

<?= UserInfoWidget::widget() ?>

<?= SettingsPage::widget() ?>
