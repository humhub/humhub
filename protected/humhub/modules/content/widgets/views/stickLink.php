<li>
    <?php if ($isSticked): ?>
        <a href="#" onClick="wallUnstick('<?= $unstickUrl; ?>'); return false;"><i class="fa fa-arrow-up"></i> <?= Yii::t('ContentModule.widgets_views_stickLink', 'Unstick'); ?></a>
       <?php else: ?>
        <a href="#" onClick="wallStick('<?= $stickUrl; ?>'); return false;"><i class="fa fa-arrow-up"></i> <?= Yii::t('ContentModule.widgets_views_stickLink', 'Stick'); ?></a>
    <?php endif; ?>
</li>
