<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;
use humhub\modules\activity\models\MailSummaryForm;
use humhub\modules\notification\components\NotificationPrivacy;
use humhub\widgets\bootstrap\Alert;
use humhub\widgets\PageHeader;

/* @var $this View */
/* @var $model MailSummaryForm */
/* @var $privacy NotificationPrivacy */

$this->params['accountPanel'] = false;
?>
<div class="d-flex flex-column gap-3">
    <?= PageHeader::title(Yii::t('ActivityModule.base', 'E-Mail Summaries')) ?>
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="text-body-secondary">
                <?= Yii::t('ActivityModule.base', 'E-Mail summaries are sent to inform you about recent activities in the network.') ?>
                <br/>
                <?= Yii::t('ActivityModule.base', 'On this page you can configure the contents and the interval of these e-mail updates.') ?>
                <br/>
            </div>

            <?php if ($privacy->isProtected()) : ?>
                <?= Alert::info($privacy === NotificationPrivacy::Minimal
                    ? Yii::t('ActivityModule.base', 'Your network sends e-mail summaries only with the number of new activities.')
                    : Yii::t('ActivityModule.base', 'Your network sends e-mail summaries without content.'))
                    ->closeButton(false)->options(['data-privacy-hint' => $privacy->value]) ?>
            <?php endif ?>

            <?= $this->render('@activity/views/mailSummaryForm', ['model' => $model]) ?>
        </div>
    </div>
</div>
