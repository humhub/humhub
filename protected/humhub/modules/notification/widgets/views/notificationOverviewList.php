<?php

use yii\helpers\Html;
?>

<?= Html::beginTag('div', $options) ?>
    <?= Html::beginTag('ul', ['class' => 'media-list']) ?>
        <?php foreach ($notifications as $notification) : ?>
            <?= $notification->render(); ?>
        <?php endforeach; ?>
        <?php if (count($notifications) == 0) : ?>
            <?= Yii::t('NotificationModule.views_overview_index', 'No notifications found!'); ?>
        <?php endif; ?>
    <?= Html::endTag('ul') ?>
    <?= Html::beginTag('center') ?>
        <?= ($pagination != null) ? \humhub\widgets\LinkPager::widget(['pagination' => $pagination]) : ''; ?>
    <?= Html::endTag('center') ?>
<?= Html::endTag('div') ?>


