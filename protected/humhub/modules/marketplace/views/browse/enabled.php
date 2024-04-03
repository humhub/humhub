<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\widgets\Button;
use humhub\widgets\ModalButton;
use humhub\widgets\ModalDialog;

/* @var string $moduleConfigUrl */
?>
<?php ModalDialog::begin([
    'header' => Yii::t('MarketplaceModule.base', 'Module <strong>enabled</strong>')
]) ?>

<div class="modal-body">
    <?php if ($moduleConfigUrl) : ?>
        <?= Yii::t('MarketplaceModule.base', 'We are almost there! As a final step, we recommend that you take a look at the module configuration, where you will find numerous configuration options, some of which are required.') ?>
        <br><br>
        <?= Yii::t('MarketplaceModule.base', 'Would you like to jump straight to it?') ?>
    <?php else : ?>
        <?= Yii::t('MarketplaceModule.base', 'Well done! You have successfully installed and enabled the module!') ?>
    <?php endif; ?>
</div>

<div class="modal-footer">
    <?php if ($moduleConfigUrl) : ?>
        <?= ModalButton::cancel(Yii::t('MarketplaceModule.base', 'No, thank you!')) ?>
        <?= Button::primary(Yii::t('MarketplaceModule.base', 'Module configuration'))
            ->link($moduleConfigUrl) ?>
    <?php else : ?>
        <?= ModalButton::primary(Yii::t('MarketplaceModule.base', 'Great!'))
            ->close() ?>
    <?php endif; ?>
</div>

<?php ModalDialog::end() ?>
