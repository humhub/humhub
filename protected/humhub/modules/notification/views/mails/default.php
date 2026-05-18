<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;
use humhub\widgets\mails\MailButton;
use humhub\widgets\mails\MailButtonList;

/* @var View $this */
/* @var string $html */
/* @var string $url */
?>
<?php $this->beginContent('@notification/views/layouts/mail.php') ?>
<?= $html ?>
<br>
<br>
<?= MailButtonList::widget(['buttons' => [
    MailButton::widget([
        'url' => $url,
        'text' => Yii::t('ContentModule.notifications', 'View Online'),
    ]),
]]) ?>
<?php $this->endContent() ?>
