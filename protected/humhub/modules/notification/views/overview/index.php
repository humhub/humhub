<?php

use yii\bootstrap5\ActiveForm;
use yii\helpers\Url;

/* @var $overview string */
/* @var $filterForm */

?>
<div class="container">
    <div class="row">
        <div class="col-md-9 layout-content-container">
            <div class="card card-default">
                <div class="card-header">
                    <?= Yii::t('NotificationModule.base', '<strong>Notification</strong> Overview'); ?>
                    <a id="notification_overview_markseen" href="#" data-action-click="notification.markAsSeen" data-action-url="<?= Url::to(['/notification/list/mark-as-seen']); ?>" class="float-end heading-link" >
                        <b><?= Yii::t('NotificationModule.base', 'Mark all as seen'); ?></b>
                    </a>
                </div>
                <div class="card-body">
                    <?= $overview ?>
                </div>
            </div>
        </div>
        <div class="col-md-3 layout-sidebar-container">
            <div class="card card-default">
                <div class="card-header">
                    <strong><?= Yii::t('NotificationModule.base', 'Filter'); ?></strong>
                    <hr style="margin-bottom: 0"/>
                </div>

                <div class="card-body">
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
