<?php

use humhub\widgets\mails\MailButtonList;

$this->beginContent('@notification/views/layouts/mail.php', $_params_); ?>
<?= $html; ?>
<br/>
<br/>
<?=
MailButtonList::widget([
    'buttons' => [
        humhub\widgets\mails\MailButton::widget(['url' => $url, 'text' => Yii::t('ContentModule.notifications', 'View Online')])
    ]
])
?>
<?php $this->endContent(); ?>
