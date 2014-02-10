=======================
Yii JCrop Extension
(c)2011 Toumal
toumaltheorca@gmail.com
=======================


This extension offers a non-intrusive yii extension that allows you to use the JCrop jQuery plugin in your yii project.
I say unintrusive because other extensions auto-insert the form fields and buttons for you, which in many cases might not be what you want.
This extension focuses on the minimum required to get you up and running, letting you do your own bells and whistles if you want to.

Optionally, this extension supports a preview of the cropped image that is dynamically updated as you move the selection rectangle.

Usage:
======

Copy/extract the yii-jcrop folder into your protected/extensions/ directory.

This is an example view that uses the widget:

<?php echo SFHtml::errorSummary($form); ?> 
<?=CHtml::beginForm(null, 'POST')?>
<? //$form in this example is of type AvatarForm, containing variables for the crop area's x, y, width and height, hence the corresponding widget form element parameters ?>
<?=CHtml::activeHiddenField($form, 'cropID');?>
<?=CHtml::activeHiddenField($form, 'cropX', array('value' => '0'));?>
<?=CHtml::activeHiddenField($form, 'cropY', array('value' => '0'));?>
<?=CHtml::activeHiddenField($form, 'cropW', array('value' => '100'));?>
<?=CHtml::activeHiddenField($form, 'cropH', array('value' => '100'));?>
<?$previewWidth = 100; $previewHeight = 100;?>
<?$this->widget('ext.jcrop.jCropWidget',array(
		'imageUrl'=>$imageUrl,
		'formElementX'=>'AvatarForm_cropX',
		'formElementY'=>'AvatarForm_cropY',
		'formElementWidth'=>'AvatarForm_cropW',
		'formElementHeight'=>'AvatarForm_cropH',
		'previewId'=>'avatar-preview', //optional preview image ID, see preview div below
		'previewWidth'=>$previewWidth,
		'previewHeight'=>$previewHeight,
		'jCropOptions'=>array(
			'aspectRatio'=>1, 
			'boxWidth'=>400,
			'boxHeight'=>400,
			'setSelect'=>array(0, 0, 100, 100),
		),
	)
);
?>

<!-- Begin optional preview box -->
<div style="position:relative; overflow:hidden; width:<?=$previewWidth?>px; height:<?=$previewHeight?>px; margin-top: 10px; margin-bottom: 10px;">
	<img id="avatar-preview" src="<?=$imageUrl?>" style="width: 0px; height: 0px; margin-left: 0px; margin-top: 0px;">
</div>
<!-- End optional preview box -->

<?=CHtml::submitButton('Crop Avatar'); ?>
<?=CHtml::endForm()?>


You are free to use any jCrop parameter in the jCropOptions array. 
If you specify javascript callback function names in such a parameter, don't forget to start them with "js:"

