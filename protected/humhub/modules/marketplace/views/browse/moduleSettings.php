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

$communityWarning = Yii::t('MarketplaceModule.base', 'These modules are provided by independent partners and developers and are <strong>not part of our curated selection</strong>.<br><br>HumHub does not review them for quality, security or compatibility. Support, updates and licensing are handled by the respective provider.<br><br>Make sure you trust the source of a module before installing it.');
$communityAck = Html::tag(
    'div',
    Html::checkbox('communityRiskAccepted', false, [
        'id' => 'community-risk-accepted',
        'class' => 'form-check-input',
    ])
    . ' '
    . Html::label(
        Yii::t('MarketplaceModule.base', 'I understand and want to continue.'),
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
        'data-confirm-header' => Yii::t('MarketplaceModule.base', 'Show non-curated modules?'),
        'data-confirm-body' => $communityConfirmBody,
        'data-confirm-text' => Yii::t('MarketplaceModule.base', 'Yes, show non-curated modules'),
        'data-cancel-text' => Yii::t('MarketplaceModule.base', 'Cancel'),
    ]) ?>

<?php Modal::endFormDialog(); ?>
