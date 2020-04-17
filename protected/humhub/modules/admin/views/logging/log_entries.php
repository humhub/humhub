<?php

use humhub\components\View;
use humhub\libs\Html;
use humhub\modules\admin\models\Log;
use humhub\widgets\Link;
use humhub\widgets\LinkPager;
use yii\data\Pagination;
use yii\log\Logger;

/* @var $this View */
/* @var $logEntries Log[] */
/* @var $pagination Pagination */
?>

<div id="admin-log-entries">
    <div>
        <?= Yii::t('AdminModule.information', 'Total {count} entries found.', ['{count}' => $pagination->totalCount]) ?>
        <span class="pull-right">
            <?= Yii::t('AdminModule.information', 'Displaying {count} entries per page.', ['{count}' => $pagination->pageSize]) ?>
        </span>
    </div>

    <?php if ($pagination->totalCount): ?>
        <hr>
    <?php endif; ?>

    <ul class="media-list">
        <?php foreach ($logEntries as $entry) : ?>

            <li class="media">
                <div class="media-body" style="word-break: break-word">

                    <?php
                    switch ($entry->level) {
                        case Logger::LEVEL_INFO:
                            $labelClass = 'label-info';
                            $levelName = Yii::t('AdminModule.information', 'Info');
                            break;
                        case Logger::LEVEL_WARNING:
                            $labelClass = 'label-warning';
                            $levelName = Yii::t('AdminModule.information', 'Warning');
                            break;
                        case Logger::LEVEL_ERROR:
                        default:
                            $labelClass = 'label-danger';
                            $levelName = Yii::t('AdminModule.information', 'Error');
                    }
                    ?>

                    <h4 class="media-heading">
                        <span class="label <?= $labelClass; ?>"><?= Html::encode($levelName) ?></span>&nbsp;
                        <?= date('r', $entry->log_time) ?>&nbsp;
                        <span class="pull-right"><?= Html::encode($entry->category) ?></span>
                    </h4>
                    <?= Html::encode($entry->message) ?>
                </div>
            </li>

        <?php endforeach; ?>
    </ul>

    <?php if ($pagination->totalCount): ?>
        <div
            class="pull-right"><?= Link::danger(Yii::t('AdminModule.information', 'Flush entries'))->post(['flush']) ?></div>
    <?php endif; ?>

    <div style="text-align: center;">
        <?= LinkPager::widget(['pagination' => $pagination]) ?>
    </div>
</div>
