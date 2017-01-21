<?php $this->beginContent('@activity/views/layouts/mail.php', $_params_); ?>
    <?php echo strip_tags(Yii::t('base', '<strong>Latest</strong> updates')); ?>

    <?= $activitiesPlaintext; ?>
<?php $this->endContent(); ?>