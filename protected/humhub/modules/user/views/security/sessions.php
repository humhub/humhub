<?php

use humhub\modules\user\widgets\security\SecuritySessionSections;

?>
<?php $this->beginContent('@user/views/security/_layout.php') ?>
<?= SecuritySessionSections::widget() ?>
<?php $this->endContent() ?>
