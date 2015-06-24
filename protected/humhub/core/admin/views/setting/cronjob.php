<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_setting_cronjob', '<strong>CronJob</strong> settings'); ?></div>
    <div class="panel-body">

        <?php
        $currentUser = '';
        if (function_exists('get_current_user')) {
            $currentUser = get_current_user();
        }
        ?>

        <p>
            <strong>Status:</strong><br/>
            <?php
            $lastRunHourly = HSetting::get('cronLastHourlyRun');
            $lastRunDaily = HSetting::get('cronLastDailyRun');

            if ($lastRunHourly == "") {
                $lastRunHourly = "<span style='color:red'>" . Yii::t('AdminModule.views_setting_cronjob', 'Never') . "</span>";
            } else {
                $lastRunHourly = HHtml::timeago($lastRunHourly);
            }
            if ($lastRunDaily == "") {
                $lastRunDaily = "<span style='color:red'>" . Yii::t('AdminModule.views_setting_cronjob', 'Never') . "</span>";
            } else {
                $lastRunDaily = HHtml::timeago($lastRunDaily);
            }
            ?>

            <?php echo Yii::t('AdminModule.views_setting_cronjob', 'Last run (hourly):'); ?> <?php echo $lastRunHourly; ?> <br/>
            <?php echo Yii::t('AdminModule.views_setting_cronjob', 'Last run (daily):'); ?> <?php echo $lastRunDaily; ?>
        </p>

        <p><?php echo Yii::t('AdminModule.views_setting_cronjob', 'Please make sure following cronjobs are installed:'); ?></p>
        <pre>

<strong><?php echo Yii::t('AdminModule.views_setting_cronjob', 'Crontab of user: {user}', array('{user}' => $currentUser)); ?></strong>
30 * * * * <?php echo Yii::app()->getBasePath(); ?>/yiic cron hourly >/dev/null 2>&1
00 18 * * * <?php echo Yii::app()->getBasePath(); ?>/yiic cron daily >/dev/null 2>&1

            <?php if ($currentUser != ""): ?>

<strong><?php echo Yii::t('AdminModule.views_setting_cronjob', 'Or Crontab of root user'); ?></strong>
*/5 * * * * su -c "<?php echo Yii::app()->getBasePath(); ?>/yiic cron hourly" <?php echo $currentUser; ?>
    >/dev/null 2>&1
0 18 * * * su -c "<?php echo Yii::app()->getBasePath(); ?>/yiic cron daily" <?php echo $currentUser; ?> >/dev/null 2>&1
            <?php endif; ?>
        </pre>

    </div>
</div>
