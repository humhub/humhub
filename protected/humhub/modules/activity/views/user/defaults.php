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
use humhub\widgets\ContentCard;

/* @var $this View */
/* @var $model MailSummaryForm */
/* @var $privacy NotificationPrivacy */

?>
<?php $this->beginContent('@notification/views/user/_layout.php') ?>
<?php ContentCard::begin(['title' => Yii::t('ActivityModule.base', 'E-Mail Summaries')]) ?>
    <?php if ($privacy->isProtected()) : ?>
        <?= Alert::info($privacy === NotificationPrivacy::Minimal
            ? Yii::t('ActivityModule.base', 'Your network sends e-mail summaries only with the number of new activities.')
            : Yii::t('ActivityModule.base', 'Your network sends e-mail summaries without content.'))
            ->closeButton(false)->options(['data-privacy-hint' => $privacy->value]) ?>
    <?php endif ?>

    <?= $this->render('@activity/views/mailSummaryForm', ['model' => $model]) ?>
<?php ContentCard::end() ?>
<?php $this->endContent() ?>
