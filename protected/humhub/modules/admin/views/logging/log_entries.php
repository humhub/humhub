<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\admin\models\Log;
use humhub\widgets\bootstrap\Badge;
use humhub\widgets\bootstrap\Link;
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
        <span class="float-end">
            <?= Yii::t('AdminModule.information', 'Displaying {count} entries per page.', ['{count}' => $pagination->pageSize]) ?>
        </span>
    </div>

    <?php if ($pagination->totalCount): ?>
        <hr>
    <?php endif; ?>

    <div id="admin-log-entry-list">
        <?php foreach ($logEntries as $entry) : ?>

            <div class="d-flex mx-2 mb-5">
                <div class="flex-grow-1 me-2" style="word-break: break-word">

                    <?php switch ($entry->level) {
                        case Logger::LEVEL_INFO:
                            $bsColor = 'info';
                            $levelName = Yii::t('AdminModule.information', 'Info');
                            break;
                        case Logger::LEVEL_WARNING:
                            $bsColor = 'warning';
                            $levelName = Yii::t('AdminModule.information', 'Warning');
                            break;
                        case Logger::LEVEL_TRACE:
                            $labelClass = 'label-default';
                            $levelName = Yii::t('AdminModule.information', 'Trace');
                            break;
                        case Logger::LEVEL_ERROR:
                        default:
                            $bsColor = 'danger';
                            $levelName = Yii::t('AdminModule.information', 'Error');
                    } ?>

                    <h4 class="mt-0">
                        <?= Badge::instance($levelName, $bsColor) ?>&nbsp;
                        <?= date('r', (int)$entry->log_time) ?>&nbsp;
                        <span class="float-end"><?= Html::encode($entry->category) ?></span>
                    </h4>
                    <div data-ui-show-more data-collapse-at="150">
                        <?= nl2br(Html::encode($entry->message)) ?>
                    </div>
                </div>
            </div>

        <?php endforeach; ?>
    </div>

    <?php if ($pagination->totalCount): ?>
        <div
            class="float-end"><?= Link::danger(Yii::t('AdminModule.information', 'Flush entries'))->post(['flush']) ?></div>
    <?php endif; ?>

    <div style="text-align: center;">
        <?= LinkPager::widget(['pagination' => $pagination]) ?>
    </div>
</div>
