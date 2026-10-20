<?php

use humhub\modules\user\widgets\security\SecurityDataSections;

?>
<?php $this->beginContent('@user/views/security/_layout.php') ?>
<?= SecurityDataSections::widget() ?>
<?php $this->endContent() ?>
