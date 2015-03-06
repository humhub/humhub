<?php if($logo->hasImage()){?>
    <a class="navbar-brand hidden-xs" style="height: 50px; padding:5px;"
    	href="<?php echo Yii::app()->createUrl('//'); ?>"> 
    	   <img class="img-rounded" src="<?php echo $logo->getUrl();?>"
    	id="img-logo"/>
    </a>
<?php } ?>
<a class="navbar-brand" style="<?php if ($logo->hasImage()) echo "display:none;"; ?>" href="<?php echo Yii::app()->createUrl('//'); ?>" id="text-logo">
    <?php echo CHtml::encode(Yii::app()->name); ?> 
</a>