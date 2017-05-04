<?php 

use yii\helpers\Url;

$this->registerJsConfig('notification', [
    'icon' => $this->theme->getBaseUrl().'/ico/notification-o.png',
    'loadEntriesUrl' => Url::to(['/notification/list']),
    'sendDesktopNotifications' => boolval(Yii::$app->notification->getDesktopNoficationSettings(Yii::$app->user->getIdentity())),
    'text' =>  [
        'placeholder' => Yii::t('NotificationModule.widgets_views_list', 'There are no notifications yet.')
    ]
]);

?>
<div id="notification_widget" data-ui-widget="notification.NotificationDropDown" data-ui-init='<?= \yii\helpers\Json::encode($update); ?>' class="btn-group">
    <a href="#" id="icon-notifications" data-action-click='toggle' aria-label="<?= Yii::t('NotificationModule.widgets_views_list', 'Open the notification dropdown menu')?>" data-toggle="dropdown" >
        <i class="fa fa-bell"></i>
    </a>
    
    <span id="badge-notifications" style="display:none;" class="label label-danger label-notifications"></span>

    <!-- container for ajax response -->
    <ul id="dropdown-notifications" class="dropdown-menu">
        <li class="dropdown-header">
            <div class="arrow"></div><?= Yii::t('NotificationModule.widgets_views_list', 'Notifications'); ?>
            <div class="dropdown-header-link">
                <a id="mark-seen-link" data-action-click='markAsSeen' data-action-url="<?= Url::to(['/notification/list/mark-as-seen']); ?>">
                    <?= Yii::t('NotificationModule.widgets_views_list', 'Mark all as seen'); ?>
                </a>
            </div>
        </li>
        <ul class="media-list"></ul>
        <li id="loader_notifications">
            <?= \humhub\widgets\LoaderWidget::widget(); ?>
        </li>
        <li>
            <div class="dropdown-footer">
                <a class="btn btn-default col-md-12" href="<?= Url::to(['/notification/overview']); ?>">
                    <?= Yii::t('NotificationModule.widgets_views_list', 'Show all notifications'); ?>
                </a>
            </div>
        </li>
    </ul>
</div>