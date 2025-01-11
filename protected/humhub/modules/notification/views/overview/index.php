<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\notification\models\forms\FilterForm;
use humhub\widgets\bootstrap\Button;
use humhub\modules\notification\widgets\NotificationFilterForm;

/* @var string $overview */
/* @var FilterForm $filterForm */
?>
<div class="container">
    <div class="row">
        <div class="col-lg-9 layout-content-container">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <?= Yii::t('NotificationModule.base', '<strong>Notification</strong> Overview') ?>
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
        <div class="col-lg-3 layout-sidebar-container">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <strong><?= Yii::t('NotificationModule.base', 'Filter') ?></strong>
                    <hr style="margin-bottom:0">
                </div>
                <div class="panel-body">
                    <?= NotificationFilterForm::widget(['filterForm' => $filterForm]) ?>
                </div>
            </div>
        </div>
    </div>
</div>
