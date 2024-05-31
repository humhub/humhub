<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\notification\models\forms\FilterForm;
use humhub\modules\notification\widgets\NotificationFilterForm;
use yii\helpers\Url;

/* @var string $overview */
/* @var FilterForm $filterForm */
?>
<div class="container">
    <div class="row">
        <div class="col-md-9 layout-content-container">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <?= Yii::t('NotificationModule.base', '<strong>Notification</strong> Overview') ?>
                    <a id="notification_overview_markseen" href="#" data-action-click="notification.markAsSeen"
                       data-action-url="<?= Url::to(['/notification/list/mark-as-seen']) ?>"
                       class="pull-right heading-link">
                        <b><?= Yii::t('NotificationModule.base', 'Mark all as seen') ?></b>
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
