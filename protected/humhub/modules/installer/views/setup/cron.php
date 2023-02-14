<?php

use yii\bootstrap\Html;

?>
<div id="cron" class="panel panel-default animated fadeIn">

    <div class="panel-heading">
        <?= Yii::t('InstallerModule.base', '<strong>Cron Jobs</strong>') ?>
    </div>

    <div class="panel-body">
        <p><?= Yii::t('InstallerModule.base', 'Asynchronous jobs in HumHub are used to run potentially heavy or scheduled tasks such as sending out summary mails, notifications or search index optimization.'); ?></p>
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
            <?= Yii::t('InstallerModule.base', 'Add following lines to the crontab:'); ?>
            <br>
            <kbd style="display: block;line-height:22px">
                <span>
                * * * * * <?= substr(get_include_path(), 2) ?> <?= $_SERVER['DOCUMENT_ROOT'] ?>/protected/yii queue/run >/dev/null 2>&1
                <br>
                * * * * * <?= substr(get_include_path(), 2) ?> <?= $_SERVER['DOCUMENT_ROOT'] ?>/protected/yii cron/run >/dev/null 2>&1
                </span>
            </kbd>
        </p>

        <p><?= Yii::t('InstallerModule.base', 'In our documentation we describe this topic in more detail: <a href="{link}">{link}</a>. If you have trouble setting up the job scheduling described in the documentation, please contact your provider to ask for support.', ['link' => 'https://docs.humhub.org/docs/admin/cron-jobs']); ?></p>
        <hr>

        <?= Html::a(Yii::t('base', 'Next'), ['/installer/setup/pretty-urls'], ['class' => 'btn btn-primary']) ?>

    </div>
</div>


