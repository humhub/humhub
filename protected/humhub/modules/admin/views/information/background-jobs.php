<?php

/**
 * @var string $driverName
 * @var int|null $lastRunHourly
 * @var int|null $lastRunDaily
 * @var int|null $waitingJobs
 * @var int|null $delayedJobs
 * @var int|null $doneJobs
 * @var int|null $reservedJobs
 * @var boolean $canClearQueue
 */

use humhub\widgets\Button;

if (empty($lastRunHourly)) {
    $lastRunHourly = "<span style='color:red'>" . Yii::t('AdminModule.information', 'Never') . "</span>";
} else {
    $lastRunHourly = Yii::$app->formatter->asRelativeTime($lastRunHourly);
}
if (empty($lastRunDaily)) {
    $lastRunDaily = "<span style='color:red'>" . Yii::t('AdminModule.information', 'Never') . "</span>";
} else {
    $lastRunDaily = Yii::$app->formatter->asRelativeTime($lastRunDaily);
}
?>
<div class="row">
    <div class="col-md-6">
        <div class="panel">
            <div class="panel-heading">
                <?= Yii::t('AdminModule.information', '<strong>CronJob</strong> Status'); ?>
            </div>
            <div class="panel-body">
                <strong><?= Yii::t('AdminModule.information', 'Last run (hourly):'); ?></strong><br> <?= $lastRunHourly; ?>
                <br/><br/>
                <strong><?= Yii::t('AdminModule.information', 'Last run (daily):'); ?></strong><br> <?= $lastRunDaily; ?>
                <br/><br/>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="panel">
            <div class="panel-heading">
                <?php if ($canClearQueue): ?>
                    <?= Button::danger('Clear queue')
                        ->link(['background-jobs', 'clearQueue' => 1])
                        ->options(['data-method' => 'POST'])
                        ->xs()->right();
                    ?>
                <?php endif; ?>
                <?= Yii::t('AdminModule.information', '<strong>Queue</strong> Status'); ?>
            </div>
            <div class="panel-body">
                <strong><?= Yii::t('AdminModule.information', 'Driver'); ?></strong><br/>
                <?= $driverName; ?><br/>
                <br/>

                <strong><?= Yii::t('AdminModule.information', 'Waiting'); ?></strong><br/>
                <?= $waitingJobs ?><br/>
                <br/>

                <strong><?= Yii::t('AdminModule.information', 'Delayed'); ?></strong><br/>
                <?= $delayedJobs ?><br/>
                <br/>

                <strong><?= Yii::t('AdminModule.information', 'Reserved'); ?></strong><br/>
                <?= $reservedJobs ?><br/>
                <br/>

                <strong><?= Yii::t('AdminModule.information', 'Done'); ?></strong><br/>
                <?= $doneJobs ?><br/>
                <br/>

            </div>
        </div>
    </div>
</div>


<p><?= Yii::t('AdminModule.information', 'Please refer to the documentation to setup the cronjobs and queue workers.'); ?></p>

