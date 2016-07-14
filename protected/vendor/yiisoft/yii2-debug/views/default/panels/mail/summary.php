<?php
/* @var $panel yii\debug\panels\MailPanel */
/* @var $mailCount integer */
if ($mailCount): ?>
<div class="yii-debug-toolbar__block">
    <a href="<?= $panel->getUrl() ?>">Mail <span class="yii-debug-toolbar__label"><?= $mailCount ?></span></a>
</div>
<?php endif ?>
