<?php

use humhub\modules\post\widgets\Form;
use humhub\modules\user\widgets\StreamViewer;
?>

<?= Form::widget(['contentContainer' => $user]); ?>
<?= StreamViewer::widget(['contentContainer' => $user]); ?>
