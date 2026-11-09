<?php

use humhub\components\View;
use humhub\modules\notification\widgets\SettingsPage;
use humhub\modules\notification\widgets\UserInfoWidget;
use humhub\widgets\ContentCard;

/* @var $this View */

// The settings island brings its own cards
ContentCard::markRendered($this);
?>
<?php $this->beginContent('@notification/views/user/_layout.php') ?>
<?= UserInfoWidget::widget() ?>
<?= SettingsPage::widget() ?>
<?php $this->endContent() ?>
