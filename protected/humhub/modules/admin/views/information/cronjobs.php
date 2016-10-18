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

    <?php echo Yii::t('AdminModule.views_setting_cronjob', 'Last run (hourly):'); ?> <?php echo $lastRunHourly; ?> <br/>
    <?php echo Yii::t('AdminModule.views_setting_cronjob', 'Last run (daily):'); ?> <?php echo $lastRunDaily; ?>
</p>

<p><?php echo Yii::t('AdminModule.views_setting_cronjob', 'Please make sure following cronjobs are installed:'); ?></p>
<pre>

<strong><?php echo Yii::t('AdminModule.views_setting_cronjob', 'Crontab of user: {user}', array('{user}' => $currentUser)); ?></strong>
30 * * * * <?php echo Yii::getAlias('@app/yii'); ?> cron/hourly >/dev/null 2>&1
00 18 * * * <?php echo Yii::getAlias('@app/yii'); ?> cron/daily >/dev/null 2>&1

<?php if ($currentUser != ""): ?>
<strong><?php echo Yii::t('AdminModule.views_setting_cronjob', 'Or Crontab of root user'); ?></strong>
*/5 * * * * su -c "<?php echo Yii::getAlias('@app/yii'); ?>  cron/hourly" <?php echo $currentUser; ?> >/dev/null 2>&1
0 18 * * * su -c "<?php echo Yii::getAlias('@app/yii'); ?>  cron/daily" <?php echo $currentUser; ?> >/dev/null 2>&1
<?php endif; ?>
</pre>

