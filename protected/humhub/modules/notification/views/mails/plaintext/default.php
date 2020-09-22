<?php $this->beginContent('@notification/views/layouts/mail_plaintext.php', $_params_); ?>

<?= $text; ?>


<?= strip_tags(Yii::t('NotificationModule.base', 'View online:')); ?> <?php echo urldecode($url); ?>
<?php $this->endContent(); ?>