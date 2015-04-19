<?php
$updatesBadge = '';
$uploadCount = '';

if (Yii::app()->getController()->id == 'module') {
    if (Yii::app()->getModule('admin')->marketplaceEnabled) {
        $updatesCount = count(Yii::app()->getController()->getOnlineModuleManager()->getModuleUpdates());
        if ($updatesCount > 0) {
            $updatesBadge = '&nbsp;&nbsp;<span class="label label-danger">' . $updatesCount . '</span>';
        } else {
            $updatesBadge = '&nbsp;&nbsp;<span class="label label-default">0</span>';
        }
    }
}
?>
<p><?php echo Yii::t('AdminModule.views_module_header', 'Modules extend the functionality of HumHub. Here you can install and manage modules from the HumHub Marketplace.') ?></p>
<hr/>
<ul class="nav nav-pills" id="moduleTabs">
    <li <?php if ($this->action->id == 'list') echo 'class="active"'; ?>><?php echo CHtml::link(Yii::t('AdminModule.views_module_header', 'Installed'), $this->createUrl('list')); ?></li>
    <?php if (Yii::app()->getModule('admin')->marketplaceEnabled) : ?>
        <li <?php if ($this->action->id == 'listOnline') echo 'class="active"'; ?>><?php echo CHtml::link(Yii::t('AdminModule.views_module_header', 'Browse online'), $this->createUrl('listOnline')); ?></li>
        <li <?php if ($this->action->id == 'listUpdates') echo 'class="active"'; ?>><?php echo CHtml::link(Yii::t('AdminModule.views_module_header', 'Available updates') . $updatesBadge, $this->createUrl('listUpdates')); ?></li>
    <?php endif; ?>
</ul>

