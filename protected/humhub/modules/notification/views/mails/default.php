<?php $this->beginContent('@notification/views/layouts/mail.php', $_params_); ?>
<?= $html; ?>
<br />
<br />
<?=
\humhub\widgets\mails\MailButtonList::widget([
    'buttons' => [
        humhub\widgets\mails\MailButton::widget(['url' => $url, 'text' => Yii::t('ContentModule.notifications', 'View Online')])
    ]
])
?>
<?php $this->endContent(); ?>