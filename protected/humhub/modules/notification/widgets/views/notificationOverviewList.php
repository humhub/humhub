<?php

use humhub\helpers\Html;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\ui\view\components\View;
use humhub\widgets\LinkPager;
use yii\data\Pagination;

/**
 * @var $this View
 * @var $notifications BaseNotification[]
 * @var $pagination Pagination
 * @var $options array
 */
?>

<?= Html::beginTag('div', $options) ?>
    <div class="hh-list">
        <?php foreach ($notifications as $notification): ?>
            <?php try { ?>
                <?= $notification->render() ?>
            <?php } catch (Throwable $t) {
                Yii::warning($t, 'notification');
            } ?>
        <?php endforeach; ?>
        <?php if (empty($notifications)): ?>
            <?= Yii::t('NotificationModule.base', 'No notifications found!'); ?>
        <?php endif; ?>
    </div>
<?php if (!empty($notifications)): ?>
    <div style="text-align: center;">
        <?= ($pagination != null) ? LinkPager::widget(['pagination' => $pagination]) : ''; ?>
    </div>
<?php endif; ?>
<?= Html::endTag('div');
