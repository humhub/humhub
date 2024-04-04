<?php

use yii\bootstrap\Html;

?>
<div id="cron" class="panel panel-default animated fadeIn">

    <div class="panel-heading">
        <?= Yii::t('InstallerModule.base', '<strong>Scheduled jobs</strong>') ?>
    </div>

    <div class="panel-body">
        <p><?= Yii::t('InstallerModule.base', 'HumHub uses cron jobs to execute scheduled or to run potentially heavy tasks such as sending out email summaries and notifications or optimizing the search index.'); ?></p>
        <p>
            <strong><?= Yii::t('InstallerModule.base', 'Installation Example:') ?></strong>
        </p>
        <p>
            <?= Yii::t('InstallerModule.base', 'Open the crontab of HumHub/PHP process user e.g. <code>{user}</code>.', ['user' => get_current_user()]) ?>
            <br>
            <kbd>
                crontab -e -u <?= get_current_user() ?>
            </kbd>
        </p>

        <p>
            <?= Yii::t('InstallerModule.base', 'Add following the lines to the crontab:'); ?>
            <br>
            <kbd style="display: block;line-height:22px">
                <span>
                * * * * * <?= Yii::getAlias('@app') ?>/yii queue/run >/dev/null 2>&1
                <br>
                * * * * * <?= Yii::getAlias('@app') ?>/yii cron/run >/dev/null 2>&1
                </span>
            </kbd>
        </p>

        <p><?= Yii::t('InstallerModule.base', 'This topic is covered in more detail in our <a href="{link}" target="_blank">documentation</a>. If you have trouble setting up the job scheduling described in the documentation, please contact your server administrator for support.', ['link' => 'https://docs.humhub.org/docs/admin/cron-jobs']); ?></p>
        <hr>

        <?= Html::a(Yii::t('base', 'Next'), ['pretty-urls'], ['class' => 'btn btn-primary']) ?>

    </div>
</div>


