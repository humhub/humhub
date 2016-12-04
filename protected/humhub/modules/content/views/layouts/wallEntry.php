<?php
/**
 * WallEntry used in a stream and the activity stream.
 *
 * @property Mixed $object a content object like Post
 * @property Content $entry the wall entry to display
 * @property String $content the output of the content object (wallOut)
 *
 * @package humhub.modules_core.wall
 * @since 0.5
 */
?>
<?php
$cssClass = ($entry->sticked) ? 'wall-entry sticked-entry' : 'wall-entry';
$isActivity = $entry->object_model == humhub\modules\activity\models\Activity::className();


if (!$isActivity) :
    ?>
    <div class="<?php echo $cssClass ?>" data-stream-entry data-stream-sticked="<?= $entry->sticked ?>"
         data-action-component="stream.StreamEntry" data-content-key="<?php echo $entry->id; ?>" >
    <?php endif; ?>

    <?php echo $content; ?>

<?php if (!$isActivity) : ?>
    </div>
<?php endif; ?>

