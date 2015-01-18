<?php
/**
 * This view represents the basic layout of a wall entry.
 *
 * @property HActiveRecordContent $object the object which this wall entry belongs to.
 *
 * @package humhub.modules_core.wall
 * @since 0.5
 */
?>
<div class="media">

    <!-- start: show wall entry options -->
    <ul class="nav nav-pills preferences">
        <li class="dropdown ">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-angle-down"></i></a>
            <ul class="dropdown-menu pull-right">
                <?php $this->widget('application.modules_core.wall.widgets.WallEntryControlsWidget', array('object' => $object)); ?>
            </ul>
        </li>
    </ul>
    <!-- end: show wall entry options -->

    <a href="<?php echo $object->content->user->getProfileUrl(); ?>" class="pull-left">
        <img class="media-object img-rounded user-image" alt="40x40" data-src="holder.js/40x40" style="width: 40px; height: 40px;"
             src="<?php echo $object->content->user->getProfileImage()->getUrl(); ?>"
             width="40" height="40"/>
    </a>

    <!-- Show space image, if you are outside from a space -->
    <?php if (Wall::$currentType != Wall::TYPE_SPACE && $object->content->container instanceof Space): ?>
        <a href="<?php echo Yii::app()->createUrl('//space/space', array('sguid' => $object->content->container->guid)); ?>"
           class="pull-left">
            <img class="media-object img-rounded img-space pull-left" data-src="holder.js/20x20" alt="20x20"
                 style="width: 20px; height: 20px;"
                 src="<?php echo $object->content->container->getProfileImage()->getUrl(); ?>">
        </a>
    <?php endif; ?>

    <div class="media-body">
        <!-- show username with link and creation time-->
        <h4 class="media-heading"><a
                href="<?php echo $object->content->user->getProfileUrl(); ?>"><?php echo $object->content->user->displayName; ?></a>
            <small><?php echo HHtml::timeago($object->content->created_at); ?>
                
                <?php if ($object->content->created_at != $object->content->updated_at): ?>
                    (<?php echo Yii::t('WallModule.views_wallLayout', 'Updated :timeago', array (':timeago'=>HHtml::timeago($object->content->updated_at))); ?>)
                <?php endif; ?>
                

                <!-- show space name -->
                <?php if (Wall::$currentType != Wall::TYPE_SPACE && $object->content->container instanceof Space): ?>
                    <?php echo Yii::t('WallModule.views_wallLayout', 'in'); ?> <strong><a href="<?php echo $object->content->container->getUrl(); ?>"><?php echo $object->content->container->name; ?></a></strong>
                <?php endif; ?>

                <!-- show labels -->
                <?php $this->widget('application.modules_core.wall.widgets.WallEntryLabelWidget', array('object' => $object)); ?>

            </small>
        </h4>
        <!-- social -->
        	<!-- youtube -->
				<?php if ($object->content->user->profile->url_youtube !=NULL): ?>
				<a href='<?php echo $object->content->user->profile->url_youtube; ?>' target="_blank"> 
					<img src="img/ytube.png">
				</a>
				<?php endif; ?>
			<!-- skype -->
				<?php if ($object->content->user->profile->im_skype !=NULL): ?>
				<a href='skype:<?php echo $object->content->user->profile->im_skype; ?>?chat'> 
					<img src="img/skype.png">
				</a>
				<?php endif; ?>
			<!-- Facebook -->
				<?php if ($object->content->user->profile->url_facebook !=NULL): ?>
				<a href='<?php echo $object->content->user->profile->url_facebook; ?>' target="_blank"> 
					<img src="img/fb.png">
				</a>
				<?php endif; ?>
		<!-- / social -->
        
        <h5><?php echo $object->content->user->profile->title; ?></h5>

    </div>
    <hr/>

    <!-- show content -->
    <div class="content" id="wall_content_<?php echo $object->getUniqueId(); ?>">
        <?php echo $content; ?>
    </div>

    <!-- show controls -->
    <?php $this->widget('application.modules_core.wall.widgets.WallEntryAddonWidget', array('object' => $object)); ?>
</div>


