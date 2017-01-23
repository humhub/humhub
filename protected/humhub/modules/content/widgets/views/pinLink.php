<li>
    <?php if ($isPinned): ?>
        <a href="#" data-action-click="unpin" data-action-url="<?= $unpinUrl ?>">
            <i class="fa fa-arrow-up"></i> <?php echo Yii::t('ContentModule.widgets_views_pinLink', 'Unpin'); ?>
        </a>
       <?php else: ?>
        <a href="#" data-action-click="pin" data-action-url="<?= $pinUrl ?>">
            <i class="fa fa-arrow-up"></i> <?php echo Yii::t('ContentModule.widgets_views_pinLink', 'Pin to top'); ?>
        </a>
    <?php endif; ?>
</li>
