<?php

use humhub\components\View;
use humhub\modules\notification\widgets\SettingsPage;
use humhub\modules\notification\widgets\UserInfoWidget;
use humhub\widgets\PageHeader;

/* @var $this View */

// The settings island brings its own cards
$this->params['accountPanel'] = false;
?>
<div class="d-flex flex-column gap-3">
    <?= PageHeader::title(Yii::t('UserModule.account', 'Notifications')) ?>
    <?= UserInfoWidget::widget() ?>
    <?= SettingsPage::widget() ?>
</div>
