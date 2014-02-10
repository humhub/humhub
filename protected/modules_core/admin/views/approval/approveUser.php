<h1><?php echo Yii::t('AdminModule.base', 'Approve user: {displayName}', array('{displayName}' => $model->displayName)); ?></h1>
<br>

<?php
$this->widget('zii.widgets.CDetailView', array(
    'data' => $model,
    'attributes' => array(
        'username',
        'email:email',
        array(
            'name' => 'group_id',
            'value' => Group::getGroupNameById($model->group_id),
        ),
        'created_at',
    ),
));
?>


<br>
<a href="<?php echo $this->createUrl('approveUserAccept', array('id' => $model->id)) ?>"
   class="btn btn-primary"><?php echo Yii::t('AdminModule.base', 'Approve membership'); ?></a>
<a href="<?php echo $this->createUrl('approveUserDecline', array('id' => $model->id)) ?>"
   class="btn btn-danger"><?php echo Yii::t('AdminModule.base', 'Decline membership'); ?></a>



