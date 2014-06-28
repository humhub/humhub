<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('SpaceModule.base', 'Current space image'); ?></div>
    <div class="panel-body">
        <img src="<?php echo $this->getController()->getSpace()->getProfileImage()->getUrl(); ?>" alt=""/><br><br>
        <?php echo CHtml::link(Yii::t('SpaceModule.base', "Change image"), $this->createUrl('//space/admin/changeImage', array('sguid' => $this->getController()->getSpace()->guid)), array('class' => 'btn btn-primary')); ?>

    </div>
</div>
<br/>
