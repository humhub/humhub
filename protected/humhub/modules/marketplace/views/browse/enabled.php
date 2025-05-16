<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\widgets\bootstrap\Button;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;

/* @var string $moduleConfigUrl */

$footer = $moduleConfigUrl ?
    ModalButton::cancel(Yii::t('MarketplaceModule.base', 'No, thank you!')) . ' ' .
    Button::primary(Yii::t('MarketplaceModule.base', 'Module configuration'))->link($moduleConfigUrl) :
    ModalButton::primary(Yii::t('MarketplaceModule.base', 'Great!'))->close();
?>

<?php Modal::beginDialog([
    'title' => Yii::t('MarketplaceModule.base', 'Module <strong>enabled</strong>'),
    'footer' => $footer,
]) ?>

    <?php if ($moduleConfigUrl) : ?>
        <?= Yii::t('MarketplaceModule.base', 'We are almost there! As a final step, we recommend that you take a look at the module configuration, where you will find numerous configuration options, some of which are required.') ?>
        <br><br>
        <?= Yii::t('MarketplaceModule.base', 'Would you like to jump straight to it?') ?>
    <?php else : ?>
        <?= Yii::t('MarketplaceModule.base', 'Well done! You have successfully installed and enabled the module!') ?>
    <?php endif; ?>

<?php Modal::endDialog() ?>
