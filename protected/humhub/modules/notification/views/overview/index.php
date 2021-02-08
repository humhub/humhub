<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Url;

/* @var $overview string */
/* @var $filterForm */

?>
<div class="container">
    <div class="row">
        <div class="col-md-9 layout-content-container">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <?= Yii::t('NotificationModule.base', '<strong>Notification</strong> Overview'); ?>
                    <a id="notification_overview_markseen" href="#" data-action-click="notification.markAsSeen" data-action-url="<?= Url::to(['/notification/list/mark-as-seen']); ?>" class="pull-right heading-link" >
                        <b><?= Yii::t('NotificationModule.base', 'Mark all as seen'); ?></b>
                    </a> 
                </div>
                <div class="panel-body">
                    <?= $overview ?>
                </div>
            </div>
        </div>
        <div class="col-md-3 layout-sidebar-container">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <strong><?= Yii::t('NotificationModule.base', 'Filter'); ?></strong>
                    <hr style="margin-bottom:0px"/>
                </div>
                
                <div class="panel-body">
                    <?php $form = ActiveForm::begin(['id' => 'notification_overview_filter', 'method' => 'GET']); ?>
                    <div style="padding-left: 5px;">
                        <?= $form->field($filterForm, 'categoryFilter')->checkboxList($filterForm->getCategoryFilterSelection())->label(false); ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
