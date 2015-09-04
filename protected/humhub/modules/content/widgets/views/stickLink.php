<li>
    <?php if ($isSticked): ?>
        <a href="#" onClick="wallUnstick('<?php echo $unstickUrl; ?>');
                    return false;"><i class="fa fa-arrow-up"></i> <?php echo Yii::t('ContentModule.widgets_views_stickLink', 'Unstick'); ?></a>
       <?php else: ?>
        <a href="#" onClick="wallStick('<?php echo $stickUrl; ?>');
                    return false;"><i class="fa fa-arrow-up"></i> <?php echo Yii::t('ContentModule.widgets_views_stickLink', 'Stick'); ?></a>
    <?php endif; ?>
</li>
