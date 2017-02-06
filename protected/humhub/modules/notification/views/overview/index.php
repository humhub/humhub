<?php

use yii\widgets\ActiveForm;
use yii\helpers\Url;

?>
<div class="container">
    <div class="row">
        <div class="col-md-9 layout-content-container">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <?= Yii::t('NotificationModule.views_overview_index', '<strong>Notification</strong> Overview'); ?>
                    <a id="notification_overview_markseen" href="#" data-action-click="notification.markAsSeen" data-action-url="<?= Url::to(['/notification/list/mark-as-seen']); ?>" class="pull-right heading-link" >
                        <b><?= Yii::t('NotificationModule.views_overview_index', 'Mark all as seen'); ?></b>
                    </a> 
                </div>
                <div class="panel-body">
                    <ul id="notification_overview_list" class="media-list">
                        <?php foreach ($notifications as $notification) : ?>
                            <?= $notification->render(); ?>
                        <?php endforeach; ?>
                        <?php if (count($notifications) == 0) : ?>
                            <?= Yii::t('NotificationModule.views_overview_index', 'No notifications found!'); ?>
                        <?php endif; ?>
                    </ul>
                    <center>
                        <?= ($pagination != null) ? \humhub\widgets\LinkPager::widget(['pagination' => $pagination]) : ''; ?>
                    </center>
                </div>
            </div>
        </div>
        <div class="col-md-3 layout-sidebar-container">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <strong><?= Yii::t('NotificationModule.views_overview_index', 'Filter'); ?></strong>
                    <hr style="margin-bottom:0px"/>
                </div>
                
                <div class="panel-body">
                    <?php $form = ActiveForm::begin(['id' => 'notification_overview_filter', 'method' => 'GET']); ?>
                    <div style="padding-left: 5px;">
                        <?= $form->field($filterForm, 'categoryFilter')->checkboxList($filterForm->getCategoryFilterSelection())->label(false); ?>
                    </div>
                    <button class="btn btn-primary btn-xm" type="submit" data-ui-loader><?= Yii::t('NotificationModule.views_overview_index', 'Apply'); ?></button>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
