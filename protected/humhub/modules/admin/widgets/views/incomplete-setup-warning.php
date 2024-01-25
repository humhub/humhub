<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.org/en/licences
 */

use humhub\libs\Html;
use humhub\modules\admin\widgets\IncompleteSetupWarning;

/* @var $this \humhub\modules\ui\view\components\View */
/* @var $problems array */

?>

<div class="card card-danger panel-invalid">
    <div class="card-header"><?= Yii::t('AdminModule.base', '<strong>Warning</strong> incomplete setup!'); ?></div>
    <div class="card-body">
        <ul>
            <?php if (in_array(IncompleteSetupWarning::PROBLEM_QUEUE_RUNNER, $problems)): ?>
                <li>
                    <?= Yii::t('AdminModule.base', 'The cron job for the background jobs (queue) does not seem to work properly.'); ?>
                </li>
            <?php endif; ?>
            <?php if (in_array(IncompleteSetupWarning::PROBLEM_CRON_JOBS, $problems)): ?>
                <li>
                    <?= Yii::t('AdminModule.base', 'The cron job for the regular tasks (cron) does not seem to work properly.'); ?>
                </li>
            <?php endif; ?>
        </ul>
        <br />
        <?php if (Yii::$app->user->isAdmin()): ?>
            <?= Html::a(Yii::t('AdminModule.base', 'Open documentation'), 'https://docs.humhub.org/docs/admin/cron-jobs', ['class' => 'btn btn-danger', 'target' => '_blank']); ?>
        <?php endif; ?>
    </div>
</div>
