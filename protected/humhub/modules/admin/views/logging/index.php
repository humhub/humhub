<?php

use humhub\compat\CHtml;
use yii\helpers\Html;
use humhub\models\Setting;
?>
<div>
    <?php echo Yii::t('AdminModule.views_logging_index', 'Total {count} entries found.', array("{count}" => $pagination->totalCount)); ?>
    <span class="pull-right"><?php echo Yii::t('AdminModule.views_logging_index', 'Displaying {count} entries per page.', array("{count}" => $pagination->pageSize)); ?></span>
</div>

<hr>
<ul class="media-list">
    <?php foreach ($logEntries as $entry) : ?>

        <li class="media">
            <div class="media-body">

                <?php
                $labelClass = "label-primary";
                if ($entry->level == \yii\log\Logger::LEVEL_WARNING) {
                    $labelClass = "label-danger";
                    $levelName = "Warning";
                } elseif ($entry->level == \yii\log\Logger::LEVEL_ERROR) {
                    $labelClass = "label-warning";
                    $levelName = "Error";
                } elseif ($entry->level == \yii\log\Logger::LEVEL_INFO) {
                    $labelClass = "label-info";
                    $levelName = "Info";
                }
                ?>

                <h4 class="media-heading">
                    <span class="label <?php echo $labelClass; ?>"><?php echo CHtml::encode($levelName); ?></span>&nbsp;
                    <?php echo date('r', $entry->log_time); ?>&nbsp;
                    <span class="pull-right"><?php echo CHtml::encode($entry->category); ?></span>
                </h4>
                <?php echo CHtml::encode($entry->message); ?>
            </div>
        </li>

    <?php endforeach; ?>
</ul>

<?php if ($pagination->totalCount != 0): ?>
    <div class="pull-right"><?php echo Html::a(Yii::t('AdminModule.views_logging_index', 'Flush entries'), array('flush'), array('class' => 'btn btn-danger', 'data-method' => 'post')); ?></div>
<?php endif; ?>

<center>
    <?= \humhub\widgets\LinkPager::widget(['pagination' => $pagination]); ?>
</center>
