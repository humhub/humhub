<h1><?php echo Yii::t('AdminModule.modules', 'Modules'); ?></h1>

<ul class="nav nav-pills" id="moduleTabs">
    <li <?php if ($this->action->id == 'list') echo 'class="active"'; ?>><?php echo CHtml::link(Yii::t('AdminModule.modules', 'Installed'), $this->createUrl('list')); ?></li>
    <li <?php if ($this->action->id == 'listOnline') echo 'class="active"'; ?>><?php echo CHtml::link(Yii::t('AdminModule.modules', 'Browse online'), $this->createUrl('listOnline')); ?></li>
    <li <?php if ($this->action->id == 'listUpdates') echo 'class="active"'; ?>><?php echo CHtml::link(Yii::t('AdminModule.modules', 'Available updates'), $this->createUrl('listUpdates')); ?></li>
</ul>
