<div class="panel panel-danger">

    <div class="panel-heading"><?php echo Yii::t('AdminModule.approval', 'New approval requests'); ?></div>
    <div class="panel-body">
        <?php echo Yii::t('AdminModule.approval', 'One or more user needs your approval as group admin.'); ?><br/><br/>
        <?php echo CHtml::link(Yii::t('AdminModule.approval', 'Click here to review'), array('//admin/approval'), array('class' => 'btn btn-danger')); ?>
    </div>

</div>

