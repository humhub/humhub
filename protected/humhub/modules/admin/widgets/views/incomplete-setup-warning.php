<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.org/en/licences
 */

use humhub\modules\admin\widgets\IncompleteSetupWarning;
use humhub\modules\ui\view\components\View;

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
            <?php if (in_array(IncompleteSetupWarning::PROBLEM_MOBILE_APP_PUSH_SERVICE, $problems)): ?>
                <li>
                    <?= Yii::t('AdminModule.base', 'The mobile app push service is not available. Please install and configure the "Push Notifications" module.') ?>
                    <?= IncompleteSetupWarning::docBtn('https://marketplace.humhub.com/module/fcm-push/installation') ?>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</div>
