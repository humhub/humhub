<?php
/**
 * This View shows a post inside the search
 *
 * @property Post $post is the post object
 *
 * @package humhub.modules.post
 * @since 0.5
 */
?>
<?php
$target = $post->content->container; // User or Space
// Get Wall Entry Id for Link
$wallEntryId = null;

// Find the right wall entry Id
foreach ($post->content->getWallEntries() as $wallEntry) {
    if ($target->wall_id == $wallEntry->wall_id) {
        $wallEntryId = $wallEntry->id;
        break;
    }
}
?>

<li>
    <a href="<?php echo Yii::app()->createUrl('space/space', array('sguid' => $target->guid, 'wallEntryId' => $wallEntryId)); ?>">

        <div class="media">
            <img class="media-object img-rounded pull-left"
                 src="<?php echo $target->getProfileImage()->getUrl(); ?>" width="50"
                 height="50" alt="50x50" data-src="holder.js/50x50" style="width: 50px; height: 50px;">

            <?php
            if ($target->status == Space::STATUS_ARCHIVED) {
                echo '<div class="archive_icon34"></div>';
            }
            ?>

            <div class="media-body">
                <strong><?php echo CHtml::encode($target->name); ?> </strong><br>

                <span class="content"><?php echo CHtml::encode(Helpers::truncateText($post->message, 200)); ?></span>

            </div>
        </div>
    </a>
</li>