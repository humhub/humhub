<?php if($logo->hasImage()){?>
    <a class="navbar-brand" style="height: 50px; padding: 0px;"
    	href="<?php echo Yii::app()->createUrl('//'); ?>"> 
    	   <img class="img-rounded" src="<?php echo $logo->getUrl();?>"
    	id="img-logo"/>
    </a>
<?php } ?>
<a class="navbar-brand hidden-xs" style="<?php if($logo->hasImage()) echo "display:none;"; ?>" href="<?php echo Yii::app()->createUrl('//'); ?>" id="text-logo">
    <?php echo Yii::app()->name; ?> 
</a>