<li>
    <?php if ($isSticked): ?>
        <a href="#" data-action-click="unstick" data-action-url="<?= $unstickUrl ?>">
            <i class="fa fa-arrow-up"></i> <?php echo Yii::t('ContentModule.widgets_views_stickLink', 'Unpinned'); ?>
        </a>
       <?php else: ?>
        <a href="#" data-action-click="stick" data-action-url="<?= $stickUrl ?>">
            <i class="fa fa-arrow-up"></i> <?php echo Yii::t('ContentModule.widgets_views_stickLink', 'Pinned'); ?>
        </a>
    <?php endif; ?>
</li>
