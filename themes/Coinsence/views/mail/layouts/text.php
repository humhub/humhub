<?= $content; ?>

---

<?php if (isset(Yii::$app->view->params['showUnsubscribe']) && Yii::$app->view->params['showUnsubscribe'] === true) : ?>
<?php $url = (isset(Yii::$app->view->params['unsubscribeUrl'])) ? Yii::$app->view->params['unsubscribeUrl'] : \yii\helpers\Url::to(['/notification/user'], true) ?>
<?= Yii::t('base', 'Unsubscribe') ?>: <?= $url ?>
<?php endif; ?>

<?= Yii::t('base', 'Powered by {name}', ['name' => 'Coinsence (https://coinsence.org)']) ?>

