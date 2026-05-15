<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;

/* @var View $this */
/* @var string $text */
/* @var string $url */
?>
<?php $this->beginContent('@notification/views/layouts/mail_plaintext.php') ?>

<?= $text ?>


<?= Yii::t('NotificationModule.base', 'View online:') ?> <?= urldecode((string) $url) ?>
<?php $this->endContent() ?>
