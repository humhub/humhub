<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.org/en/licences
 */

use humhub\components\View;
use humhub\modules\admin\widgets\IncompleteSetupWarning;

/* @var $this View */
/* @var $problems array */

?>

<div class="panel panel-danger panel-invalid">
    <div class="panel-heading"><?= Yii::t('AdminModule.base', '<strong>Warning</strong> incomplete setup!'); ?></div>
    <div class="panel-body">
        <ul>
            <?php if (in_array(IncompleteSetupWarning::PROBLEM_QUEUE_RUNNER, $problems)): ?>
                <li>
                    <?= Yii::t('AdminModule.base', 'The cron job for the background jobs (queue) does not seem to work properly.') ?>
                    <?= IncompleteSetupWarning::docBtn('https://docs.humhub.org/docs/admin/cron-jobs') ?>
                </li>
            <?php endif; ?>
            <?php if (in_array(IncompleteSetupWarning::PROBLEM_CRON_JOBS, $problems)): ?>
                <li>
                    <?= Yii::t('AdminModule.base', 'The cron job for the regular tasks (cron) does not seem to work properly.') ?>
                    <?= IncompleteSetupWarning::docBtn('https://docs.humhub.org/docs/admin/cron-jobs') ?>
                </li>
            <?php endif; ?>
            <?php if (in_array(IncompleteSetupWarning::PROBLEM_DOCUMENT_ROOT_EXPOSED, $problems)): ?>
                <li>
                    <?= Yii::t('AdminModule.base', 'The installation directory is reachable over the web, which exposes your configuration, uploads and installed modules. Point the document root of your web server to the "public" directory.') ?>
                    <?= IncompleteSetupWarning::docBtn('https://docs.humhub.org/docs/admin/installation') ?>
                </li>
            <?php endif; ?>
            <?php if (in_array(IncompleteSetupWarning::PROBLEM_LEGACY_ENTRY_SCRIPT, $problems)): ?>
                <li>
                    <?= Yii::t('AdminModule.base', 'This installation is still served through the deprecated entry script in the installation directory, which exposes your configuration, uploads and installed modules. Point the document root of your web server to the "public" directory. Support for the old entry script will be removed in a future version.') ?>
                    <?= IncompleteSetupWarning::docBtn('https://docs.humhub.org/docs/admin/installation') ?>
                </li>
            <?php endif; ?>
            <?php if (in_array(IncompleteSetupWarning::PROBLEM_MOBILE_APP_PUSH_SERVICE, $problems)): ?>
                <li>
                    <?= Yii::t('AdminModule.base', 'The mobile app push service is not available. Please install and configure the "Push Notifications" module.') ?>
                    <?= IncompleteSetupWarning::docBtn('https://marketplace.humhub.com/module/fcm-push/installation') ?>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</div>
