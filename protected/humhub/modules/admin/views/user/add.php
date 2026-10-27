<?php

use humhub\compat\HForm;
use humhub\modules\user\services\LinkRegistrationService;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\ContentCard;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\modal\ModalButton;

/**
 * @var $hForm HForm
 * @var $canInviteByEmail bool
 * @var $canInviteByLink bool
 */
?>

<?php ContentCard::begin([
    'title' => Yii::t('AdminModule.user', 'Add new user'),
    'backUrl' => ['index'],
    'backLabel' => Yii::t('AdminModule.base', 'Back to overview'),
    'buttons' => ($canInviteByEmail || $canInviteByLink
            ? ModalButton::success(Yii::t('AdminModule.user', 'Invite new people'))
                ->load(['/user/invite', 'target' => LinkRegistrationService::TARGET_ADMIN])
                ->icon('invite')
                ->sm()
            : ''),
]) ?>
<?php $form = ActiveForm::begin(['options' => ['data-ui-widget' => 'ui.form.TabbedForm', 'data-ui-init' => ''], 'acknowledge' => true]); ?>
<?= $hForm->render($form); ?>
<?php ActiveForm::end(); ?>
<?php ContentCard::end() ?>
