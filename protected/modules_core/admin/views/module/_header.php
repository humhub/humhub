<ul class="nav nav-pills" id="moduleTabs">
    <li <?php if ($this->action->id == 'list') echo 'class="active"'; ?>><?php echo CHtml::link(Yii::t('AdminModule.views_module_header', 'Installed'), $this->createUrl('list')); ?></li>
    <li <?php if ($this->action->id == 'listOnline') echo 'class="active"'; ?>><?php echo CHtml::link(Yii::t('AdminModule.views_module_header', 'Browse online'), $this->createUrl('listOnline')); ?></li>
    <li <?php if ($this->action->id == 'listUpdates') echo 'class="active"'; ?>><?php echo CHtml::link(Yii::t('AdminModule.views_module_header', 'Available updates'), $this->createUrl('listUpdates')); ?></li>
</ul>
<hr/>
