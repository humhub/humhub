<?php
/**
 * @deprecated since 1.3
 */
?>

<?= $this->render('@ui/menu/widgets/views/sub-tab-menu.php', ['options' => ['class' => 'nav nav-tabs tab-sub-menu'], 'entries' => $this->context->getSortedEntries()]) ?>
