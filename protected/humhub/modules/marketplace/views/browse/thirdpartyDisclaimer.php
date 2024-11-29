<?php

use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;

?>

<?php Modal::beginDialog([
    'title' => Yii::t('MarketplaceModule.base', 'Third-party disclaimer'),
    'footer' => ModalButton::cancel(Yii::t('MarketplaceModule.base', 'Ok')),
]) ?>

    <p>
        <?= Yii::t('MarketplaceModule.base', 'This Module was developed by a third-party.') ?>
        <?= Yii::t('MarketplaceModule.base', 'The HumHub project does not guarantee the functionality, quality or the continuous development of this Module.') ?>
    </p>

    <p>
        <?= Yii::t('MarketplaceModule.base', 'Third-party Modules are not covered by Professional Edition agreements.') ?>
    </p>

    <p>
        <?= Yii::t('MarketplaceModule.base', 'If this Module is additionally marked as <strong>"Community"</strong> it is neither tested nor monitored by the HumHub project team.') ?>
    </p>

<?php Modal::endDialog() ?>
