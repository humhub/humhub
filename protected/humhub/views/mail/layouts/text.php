<?php

use yii\helpers\Url;
?>
<?= $content; ?>


---

<?php if (isset(Yii::$app->view->params['showUnsubscribe']) && Yii::$app->view->params['showUnsubscribe'] === true) : ?>
    <span style="text-decoration: none; color: #a3a2a2;">
        <a href="<?= Url::to(['/user/account/emailing'], true) ?>" style="text-decoration: none; color: #a3a2a2;"><?= Yii::t('base', 'Unsubscribe') ?></a>
        • 
    </span> 
<?php endif; ?>

Powered by HumHub (http://www.humhub.org)