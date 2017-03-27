<p>
    <strong>Status:</strong><br/>
    <?php
    if ($lastRunHourly == "") {
        $lastRunHourly = "<span style='color:red'>" . Yii::t('AdminModule.views_setting_cronjob', 'Never') . "</span>";
    } else {
        $lastRunHourly = \humhub\widgets\TimeAgo::widget(['timestamp' => $lastRunHourly]);
    }
    if ($lastRunDaily == "") {
        $lastRunDaily = "<span style='color:red'>" . Yii::t('AdminModule.views_setting_cronjob', 'Never') . "</span>";
    } else {
        $lastRunDaily = \humhub\widgets\TimeAgo::widget(['timestamp' => $lastRunDaily]);
    }
    ?>

    <?= Yii::t('AdminModule.views_setting_cronjob', 'Last run (hourly):'); ?> <?= $lastRunHourly; ?> <br/>
    <?= Yii::t('AdminModule.views_setting_cronjob', 'Last run (daily):'); ?> <?= $lastRunDaily; ?>
</p>

<p><?= Yii::t('AdminModule.views_setting_cronjob', 'Please make sure following cronjobs are installed:'); ?></p>
<pre>

<strong><?= Yii::t('AdminModule.views_setting_cronjob', 'Crontab of user: {user}', array('{user}' => $currentUser)); ?></strong>
30 * * * * <?= Yii::getAlias('@app/yii'); ?> cron/hourly >/dev/null 2>&1
00 18 * * * <?= Yii::getAlias('@app/yii'); ?> cron/daily >/dev/null 2>&1

<?php if ($currentUser != ""): ?>
<strong><?= Yii::t('AdminModule.views_setting_cronjob', 'Or Crontab of root user'); ?></strong>
*/5 * * * * su -c "<?= Yii::getAlias('@app/yii'); ?>  cron/hourly" <?= $currentUser; ?> >/dev/null 2>&1
0 18 * * * su -c "<?= Yii::getAlias('@app/yii'); ?>  cron/daily" <?= $currentUser; ?> >/dev/null 2>&1
<?php endif; ?>
</pre>