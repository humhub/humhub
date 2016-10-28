<li>
    <?php if ($isSticked): ?>
        <a href="#" data-action-click="unstick" data-action-url="<?= $unstickUrl ?>">
            <i class="fa fa-arrow-up"></i> <?php echo Yii::t('ContentModule.widgets_views_stickLink', 'Unstick'); ?>
        </a>
       <?php else: ?>
        <a href="#" data-action-click="stick" data-action-url="<?= $stickUrl ?>">
            <i class="fa fa-arrow-up"></i> <?php echo Yii::t('ContentModule.widgets_views_stickLink', 'Stick'); ?>
        </a>
    <?php endif; ?>
</li>
