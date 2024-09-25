<?php

use humhub\widgets\modal\ModalButton;

?>

<?= ModalButton::primary(Yii::t('SpaceModule.base', 'Invite'))
    ->load($space->createUrl('/space/membership/invite'))->icon('invite') ?>
