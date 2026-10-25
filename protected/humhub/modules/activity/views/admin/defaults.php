<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/* @var $model MailSummaryForm */
/* @var $privacy NotificationPrivacy */
/* @var $privacyFixed bool */

use humhub\helpers\Html;
use humhub\modules\activity\models\MailSummaryForm;
use humhub\modules\notification\components\NotificationPrivacy;
use humhub\widgets\bootstrap\Alert;
use humhub\widgets\ContentCard;

?>

<?php ContentCard::begin([
    'title' => Yii::t('ActivityModule.base', 'E-Mail Summaries'),
    'description' => Yii::t('ActivityModule.base', 'On this page you can define the default behavior for your users. These settings can be overwritten by users in their account settings page.'),
]) ?>
<?php if ($privacy->isProtected()) : ?>
    <?= Alert::info(
        Yii::t('ActivityModule.base', 'Privacy mode: {mode}.', [
            '{mode}' => $privacy === NotificationPrivacy::Minimal
                ? Yii::t('ActivityModule.base', 'Minimal')
                : Yii::t('ActivityModule.base', 'Hide content'),
        ]) . ' '
        . ($privacy === NotificationPrivacy::Minimal
            ? Yii::t('ActivityModule.base', 'E-mail summaries only show the number of new activities.')
            : Yii::t('ActivityModule.base', 'E-mail summaries are sent without content.')) . ' '
        . ($privacyFixed
            ? Yii::t('ActivityModule.base', 'It is set in the configuration file.')
            : Yii::t('ActivityModule.base', 'Change it under {link}.', [
                '{link}' => Html::a(Yii::t('ActivityModule.base', 'Notifications'), ['/notification/admin/defaults']),
            ])),
    )->closeButton(false)->options(['data-privacy-hint' => $privacy->value]) ?>
<?php endif ?>

<?= $this->render('@activity/views/mailSummaryForm', ['model' => $model]) ?>
<?php ContentCard::end() ?>
