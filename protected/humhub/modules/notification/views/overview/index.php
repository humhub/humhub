<?php

use humhub\modules\notification\models\forms\FilterForm;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;

/* @var $overview string */
/* @var $filterForm FilterForm */
?>
<div class="container">
    <div class="row">
        <div class="col-md-9 layout-content-container">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <?= Yii::t('NotificationModule.base', '<strong>Notification</strong> Overview'); ?>
                    <div class="float-end">
                        <?= Button::light()
                            ->icon('check')
                            ->action('notification.markAsSeen', ['/notification/list/mark-as-seen'])
                            ->id('notification_overview_markseen')
                            ->style('display:none')
                            ->sm()
                            ->tooltip(Yii::t('NotificationModule.base', 'Mark all as seen')) ?>
                        <?= Button::light()
                            ->icon('cog')
                            ->link(['/notification/user'])
                            ->sm()
                            ->tooltip(Yii::t('NotificationModule.base', 'Notification Settings')) ?>
                    </div>
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
