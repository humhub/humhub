<?php

use yii\helpers\Url;
use yii\helpers\Html;

/* @var $options [] */

?>
<?= Html::beginTag('div', $options) ?>
<a href="#" id="icon-notifications" data-action-click='toggle'
   aria-label="<?= Yii::t('NotificationModule.base', 'Open the notification dropdown menu') ?>" data-bs-toggle="dropdown">
    <i class="fa fa-bell"></i>
</a>

<span id="badge-notifications" style="display:none;" class="label label-danger label-notifications"></span>

<!-- container for ajax response -->
<ul id="dropdown-notifications" class="dropdown-menu">
    <li class="dropdown-item dropdown-header">
        <div class="arrow"></div><?= Yii::t('NotificationModule.base', 'Notifications'); ?>
        <div class="dropdown-header-link">
            <a id="mark-seen-link" data-action-click='markAsSeen'
               data-action-url="<?= Url::to(['/notification/list/mark-as-seen']); ?>">
                <?= Yii::t('NotificationModule.base', 'Mark all as seen'); ?>
            </a>
        </div>
    </li>
    <li class="dropdown-item">
        <ul class="media-list"></ul>
    </li>
    <li class="dropdown-item" id="loader_notifications">
        <?= \humhub\widgets\LoaderWidget::widget(); ?>
    </li>
    <li class="dropdown-item">
        <div class="dropdown-footer">
            <a class="btn btn-outline-secondary col-md-12" href="<?= Url::to(['/notification/overview']); ?>">
                <?= Yii::t('NotificationModule.base', 'Show all notifications'); ?>
            </a>
        </div>
    </li>
</ul>
<?= Html::endTag('div') ?>
