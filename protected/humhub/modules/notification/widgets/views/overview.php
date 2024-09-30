<?php

use humhub\helpers\Html;
use humhub\widgets\bootstrap\Badge;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\LoaderWidget;
use yii\helpers\Url;

/* @var $options [] */

?>
<?= Html::beginTag('div', $options) ?>
<a href="#" id="icon-notifications" data-action-click='toggle'
   aria-label="<?= Yii::t('NotificationModule.base', 'Open the notification dropdown menu') ?>" data-bs-toggle="dropdown">
    <i class="fa fa-bell"></i>
</a>

<?= Badge::danger()
    ->id('badge-notifications')
    ->cssClass('label-notifications')
    ->style('display:none;') ?>

<!-- container for ajax response -->
<ul id="dropdown-notifications" class="dropdown-menu">
    <li class="dropdown-header">
        <div class="arrow"></div>
        <?= Yii::t('NotificationModule.base', 'Notifications') ?>
        <div class="dropdown-header-actions">
            <?= Button::secondary()
                ->icon('check')
                ->action('markAsSeen', ['/notification/list/mark-as-seen'])
                ->id('mark-seen-link')
                ->style('display:none')
                ->sm()
                ->tooltip(Yii::t('NotificationModule.base', 'Mark all as seen')) ?>
            <?= Button::secondary()
                ->icon('cog')
                ->link(['/notification/user'])
                ->sm()
                ->tooltip(Yii::t('NotificationModule.base', 'Notification Settings')) ?>
        </div>
    </li>
    <li>
        <div class="media-list"></div>
    </li>
    <li id="loader_notifications">
        <?= LoaderWidget::widget() ?>
    </li>
    <li>
        <div class="dropdown-footer">
            <a class="btn btn-secondary col-md-12" href="<?= Url::to(['/notification/overview']) ?>">
                <?= Yii::t('NotificationModule.base', 'Show all notifications') ?>
            </a>
        </div>
    </li>
</ul>
<?= Html::endTag('div') ?>
