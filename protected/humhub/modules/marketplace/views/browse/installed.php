<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;

/* @var int $moduleId */
?>
<?php Modal::beginDialog([
    'title' => Yii::t('MarketplaceModule.base', 'Module <strong>installed</strong>'),
    'footer' =>
        ModalButton::cancel(Yii::t('MarketplaceModule.base', 'No, thank you!')) . ' ' .
        ModalButton::primary(Yii::t('MarketplaceModule.base', 'Enable now'))
            ->action('marketplace.enable', ['/marketplace/browse/enable'])
            ->options(['data-module-id' => $moduleId]),
]) ?>

    <?= Yii::t('MarketplaceModule.base', 'Well done! To make the module available within your network, you will also need to enable it. Do you want to enable it now?') ?>

<?php Modal::endDialog() ?>
