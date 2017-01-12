<?php
/* @var $model \humhub\modules\notification\models\forms\NotificationSettings */
?>

<div class="panel-body">
    <h4><?= Yii::t('ActivityModule.base', 'E-Mail Summaries'); ?></h4>
    <div class="help-block">
        <?= Yii::t('ActivityModule.base', 'E-Mail summaries are sent to inform you about recent activities in the network.'); ?><br />
        <?= Yii::t('ActivityModule.base', 'On this page you can configure the contents and the interval of these e-mail updates.'); ?><br />
        <br />
    </div>

    <?= $this->render('@activity/views/mailSummaryForm', ['model' => $model]); ?>

    <br />
</div>

