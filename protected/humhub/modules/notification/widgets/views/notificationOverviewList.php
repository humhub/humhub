<?php

use humhub\widgets\LinkPager;
use yii\helpers\Html;
?>

<?= Html::beginTag('div', $options) ?>
    <?= Html::beginTag('ul', ['class' => 'media-list']) ?>
        <?php foreach ($notifications as $notification) : ?>
            <?= $notification->render(); ?>
        <?php endforeach; ?>
        <?php if (empty($notifications)) : ?>
            <?= Yii::t('NotificationModule.base', 'No notifications found!'); ?>
        <?php endif; ?>
    <?= Html::endTag('ul') ?>
    <?php if (!empty($notifications)) : ?>
        <?= Html::beginTag('center') ?>
            <?= ($pagination != null) ? LinkPager::widget(['pagination' => $pagination]) : ''; ?>
        <?= Html::endTag('center') ?>
    <?php endif;?>
<?= Html::endTag('div') ?>


