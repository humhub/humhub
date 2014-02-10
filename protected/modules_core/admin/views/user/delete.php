
<h1><?php echo Yii::t('AdminModule.base', 'Delete user: {username}', array('{username}' => $model->username)); ?></h1>


<p>
    <?php echo Yii::t('AdminModule.base', 'Are you sure you want to delete this user? If this user is owner of some spaces, <b>you</b> will become owner of these spaces.'); ?>
</p>

<?php
$this->widget('zii.widgets.CDetailView', array(
    'data' => $model,
    'attributes' => array(
        'username',
        'firstname',
        'lastname',
        'email:email',
        'created_at',
    ),
));
?>

<br />
<?php echo HHtml::postLink(Yii::t('AdminModule.base', 'Delete user'), $this->createUrl('//admin/user/delete', array('id' => $model->id, 'doit' => 2)), array('class' => 'btn btn-danger')); ?>
&nbsp;
<?php echo CHtml::link(Yii::t('base', 'Back'), $this->createUrl('//admin/user/edit', array('id' => $model->id)), array('class' => 'btn btn-primary')); ?>


