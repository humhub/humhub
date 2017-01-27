<?php

use yii\helpers\Url;
?>
<?= $content; ?>


---

<?php if (isset(Yii::$app->view->params['showUnsubscribe']) && Yii::$app->view->params['showUnsubscribe'] === true) : ?>
<?= Yii::t('base', 'Unsubscribe') ?>: <?= Url::to(['/user/account/emailing'], true) ?>
<?php endif; ?>

Powered by HumHub (http://www.humhub.org)