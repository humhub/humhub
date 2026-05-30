<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2022 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\helpers\Html;
use humhub\modules\marketplace\models\forms\GeneralModuleSettingsForm;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;

/* @var GeneralModuleSettingsForm $settings */

$communityWarning = Yii::t('MarketplaceModule.base', 'Community modules are developed by third parties and are <strong>not tested or maintained by the HumHub team</strong>.<br><br>They may not be compatible with your HumHub version, can cause <strong>instability or unexpected behavior</strong>, and may stop working after future updates. Their long-term maintenance is not guaranteed.<br><br>Only enable this option if you understand the risks and trust the source of the module you intend to install.');
$communityAck = Html::tag(
    'div',
    Html::checkbox('communityRiskAccepted', false, [
        'id' => 'community-risk-accepted',
        'class' => 'form-check-input',
    ])
    . ' '
    . Html::label(
        Yii::t('MarketplaceModule.base', 'I understand the risk and want to continue.'),
        'community-risk-accepted',
        ['class' => 'form-check-label'],
    ),
    ['class' => 'form-check mt-3'],
);
$communityConfirmBody = $communityWarning . $communityAck;
?>

<?php $form = Modal::beginFormDialog([
    'title' => Yii::t('MarketplaceModule.base', '<strong>General</strong> Settings'),
    'footer' => ModalButton::cancel() . ' ' . ModalButton::save()->submit(),
]) ?>

    <?= $form->field($settings, 'includeBetaUpdates')->checkbox() ?>

    <?= $form->field($settings, 'includeCommunityModules')->checkbox([
        'data-action-change' => 'marketplace.toggleCommunity',
        'data-confirm-header' => Yii::t('MarketplaceModule.base', 'Include unverified community modules?'),
        'data-confirm-body' => $communityConfirmBody,
        'data-confirm-text' => Yii::t('MarketplaceModule.base', 'Yes, show community modules'),
        'data-cancel-text' => Yii::t('MarketplaceModule.base', 'Cancel'),
    ]) ?>

<?php Modal::endFormDialog(); ?>
