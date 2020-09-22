<?php
/**
 * @deprecated since 1.3
 */
?>

<?= $this->render('@ui/menu/widgets/views/tab-menu.php', ['options' => ['class' => 'tab-menu'], 'entries' => $this->context->getSortedEntries()]) ?>
