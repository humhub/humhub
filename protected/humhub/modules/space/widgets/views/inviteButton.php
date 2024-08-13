<?php
use humhub\widgets\ModalButton;
?>

<?= ModalButton::primary(Yii::t('SpaceModule.base', 'Invite'))
    ->load($space->createUrl('/space/membership/invite'))->icon('invite') ?>
