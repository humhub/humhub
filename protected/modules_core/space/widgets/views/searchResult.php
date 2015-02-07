<?php
/**
 * This View shows a space inside the search
 *
 * @property Space $space is the space object
 *
 * @package humhub.modules.space
 * @since 0.5
 */
?>
<li>
    <a href="<?php echo $space->getUrl(); ?>">

        <div class="media">
            <img class="media-object img-rounded pull-left" src="<?php echo $space->getProfileImage()->getUrl(); ?>"
                 width="50"
                 height="50" alt="50x50" data-src="holder.js/50x50" style="width: 50px; height: 50px;">

            <?php
            if ($space->status == Space::STATUS_ARCHIVED) {
                echo '<div class="archive_icon34"></div>';
            }
            ?>

            <div class="media-body">
                <h4 class="media-heading"><?php echo CHtml::encode($space->name); ?></h4>
                <span><?php echo CHtml::encode(Helpers::truncateText($space->description, 30)); ?></span>

            </div>
        </div>
    </a>
</li>