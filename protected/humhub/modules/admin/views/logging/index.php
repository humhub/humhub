<?php

use yii\helpers\Html;
use humhub\compat\CHtml;

/* @var $logEntries \humhub\modules\admin\models\Log[] */
/* @var $pagination \yii\data\Pagination */

?>
<div>
    <?= Yii::t('AdminModule.information', 'Total {count} entries found.', ["{count}" => $pagination->totalCount]); ?>
    <span class="pull-right"><?= Yii::t('AdminModule.information', 'Displaying {count} entries per page.', ["{count}" => $pagination->pageSize]); ?></span>
</div>

<hr>
<ul class="media-list">
    <?php foreach ($logEntries as $entry) : ?>

        <li class="media">
            <div class="media-body">

                <?php
                $labelClass = "label-primary";
                if ($entry->level == \yii\log\Logger::LEVEL_WARNING) {
                    $labelClass = "label-warning";
                    $levelName = "Warning";
                } elseif ($entry->level == \yii\log\Logger::LEVEL_ERROR) {
                    $labelClass = "label-danger";
                    $levelName = "Error";
                } elseif ($entry->level == \yii\log\Logger::LEVEL_INFO) {
                    $labelClass = "label-info";
                    $levelName = "Info";
                }
                ?>

                <h4 class="media-heading">
                    <span class="label <?= $labelClass; ?>"><?= CHtml::encode($levelName); ?></span>&nbsp;
                    <?= date('r', $entry->log_time); ?>&nbsp;
                    <span class="pull-right"><?= CHtml::encode($entry->category); ?></span>
                </h4>
                <?= CHtml::encode($entry->message); ?>
            </div>
        </li>

    <?php endforeach; ?>
</ul>

<?php if ($pagination->totalCount != 0): ?>
    <div class="pull-right"><?= Html::a(Yii::t('AdminModule.information', 'Flush entries'), ['flush'], ['class' => 'btn btn-danger', 'data-method' => 'post']); ?></div>
<?php endif; ?>

<center>
    <?= \humhub\widgets\LinkPager::widget(['pagination' => $pagination]); ?>
</center>
