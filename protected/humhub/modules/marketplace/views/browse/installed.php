<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\widgets\ModalButton;
use humhub\widgets\ModalDialog;

/* @var int $moduleId */
?>
<?php ModalDialog::begin([
    'header' => Yii::t('MarketplaceModule.base', 'Module <strong>installed</strong>')
]) ?>

    <div class="modal-body">
        <?= Yii::t('MarketplaceModule.base', 'Well done! To make the module available within your network, you will also need to activate it. Do you want to activate it now?') ?>
    </div>

    <div class="modal-footer">
        <?= ModalButton::cancel() ?>
        <?= ModalButton::primary(Yii::t('MarketplaceModule.base', 'Activate now'))
            ->action('marketplace.activate', ['/marketplace/browse/activate'])
            ->options(['data-module-id' => $moduleId]) ?>
    </div>

<?php ModalDialog::end() ?>
