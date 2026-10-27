<?php

/**
 * @var string $driverName
 * @var int|null $lastRunHourly
 * @var int|null $lastRunDaily
 * @var int|null $waitingJobs
 * @var int|null $delayedJobs
 * @var int|null $doneJobs
 * @var int|null $reservedJobs
 * @var bool $canClearQueue
 */

use humhub\widgets\bootstrap\Button;
use humhub\widgets\ContentCard;

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
<?php ContentCard::begin([
    'title' => Yii::t('AdminModule.information', 'Background Jobs'),
    'description' => Yii::t('AdminModule.information', 'Please refer to the documentation to setup the cronjobs and queue workers.'),
]) ?>
<div class="row gy-3">
    <section class="col-lg-6">
        <h3><?= strip_tags(Yii::t('AdminModule.information', '<strong>CronJob</strong> Status')) ?></h3>
        <strong><?= Yii::t('AdminModule.information', 'Last run (hourly):') ?></strong><br> <?= $lastRunHourly ?>
        <br/><br/>
        <strong><?= Yii::t('AdminModule.information', 'Last run (daily):') ?></strong><br> <?= $lastRunDaily ?>
    </section>

    <section class="col-lg-6">
        <h3>
            <?= strip_tags(Yii::t('AdminModule.information', '<strong>Queue</strong> Status')) ?>
            <?php if ($canClearQueue): ?>
                <?= Button::danger('Clear queue')
                    ->link(['background-jobs', 'clearQueue' => 1])
                    ->options(['data-method' => 'POST'])
                    ->sm()->right() ?>
            <?php endif; ?>
        </h3>
        <strong><?= Yii::t('AdminModule.information', 'Driver') ?></strong><br/>
        <?= $driverName ?><br/>
        <br/>

        <strong><?= Yii::t('AdminModule.information', 'Waiting') ?></strong><br/>
        <?= $waitingJobs ?><br/>
        <br/>

        <strong><?= Yii::t('AdminModule.information', 'Delayed') ?></strong><br/>
        <?= $delayedJobs ?><br/>
        <br/>

        <strong><?= Yii::t('AdminModule.information', 'Reserved') ?></strong><br/>
        <?= $reservedJobs ?><br/>
        <br/>

        <strong><?= Yii::t('AdminModule.information', 'Done') ?></strong><br/>
        <?= $doneJobs ?>
    </section>
</div>
<?php ContentCard::end() ?>
