<?php $this->beginContent('@notification/views/layouts/mail_plaintext.php', $_params_); ?>

<?php
 /* @var $text string */
 /* @var $url string */
?>

<?= $text ?>


<?= Yii::t('NotificationModule.base', 'View online:') ?> <?= urldecode($url) ?>
<?php $this->endContent(); ?>
