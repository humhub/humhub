<?php

use humhub\modules\installer\forms\SampleDataForm;
use humhub\modules\ui\form\widgets\ActiveForm;
use yii\bootstrap\Html;

?>
<div id="cron" class="panel panel-default animated fadeIn">

    <div class="panel-heading">
        <?= Yii::t('InstallerModule.base', '<strong>Cron Jobs</strong>') ?>
    </div>

    <div class="panel-body">
        <p><?= Yii::t('InstallerModule.base', 'Asynchronous jobs in HumHub are used to run potentially heavy or scheduled tasks such as sending out summary mails, notifications or search index optimization.'); ?></p>
        <p>
            <strong><?= Yii::t('InstallerModule.base', 'Cron job setup steps:') ?></strong>
        </p>
        <p>
            <?= Yii::t('InstallerModule.base', 'Open the crontab of HumHub/PHP process user e.g. <code>www-data</code>.') ?>
            <br>
            <kbd>
                crontab -e -u www-data
            </kbd>
        </p>

        <p>
            <?= Yii::t('InstallerModule.base', 'Add following line to the crontab:'); ?>
            <br>
            <kbd style="display: block; padding: 0.75rem 1rem;">
                <span>
                * * * * * /usr/bin/php /var/www/humhub/protected/yii queue/run >/dev/null 2>&1
                <br>
                * * * * * /usr/bin/php /var/www/humhub/protected/yii cron/run >/dev/null 2>&1
                </span>
            </kbd>
        </p>

        <p><?= Yii::t('InstallerModule.base', 'Make sure to replace <code>/var/www/humhub</code> with the path of your HumHub installation.'); ?></p>

        <p><?= Yii::t('InstallerModule.base', 'In our documentation we describe this topic in more detail: <a href="{link}">{link}</a>. If you have trouble setting up the job scheduling described in the documentation, please contact your provider to ask for support.', ['link' => 'https://docs.humhub.org/docs/admin/cron-jobs']); ?></p>

        <hr>

        <?= Html::a(Yii::t('base', 'Next'), Yii::$app->getModule('installer')->getNextConfigStepUrl(), ['class' => 'btn btn-primary']) ?>
    </div>
</div>


