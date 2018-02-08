<?php

use humhub\modules\post\widgets\Form;
use humhub\modules\user\widgets\StreamViewer;
?>

<?php $this->beginContent('@user/views/profile/_sidebar.php', ['user' => $user]); ?>

<?= Form::widget(['contentContainer' => $user]); ?>
<?= StreamViewer::widget(['contentContainer' => $user]); ?>

<?= $this->endContent(); ?>