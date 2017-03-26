<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */
 
/* @var $model \humhub\modules\notification\models\forms\NotificationSettings */
?>

<div class="panel-body">
    <h4><?= Yii::t('ActivityModule.base', 'E-Mail Summaries'); ?></h4>
    <div class="help-block">
        <?= Yii::t('ActivityModule.base', 'E-Mail summaries are sent to users to inform them about recent activities in your network.'); ?><br>
        <?= Yii::t('ActivityModule.base', 'On this page you can define the default behavior for your users. These settings can be overwritten by users in their account settings page.'); ?>
        <br>
    </div>
    <br>

    <?= $this->render('@activity/views/mailSummaryForm', ['model' => $model]); ?>
</div>