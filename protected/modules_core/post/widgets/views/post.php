<?php
/**
 * This view represents a wall entry of a post.
 * Used by PostWidget to show Posts inside a wall.
 *
 * @property User $user the user which created this post
 * @property Post $post the current post
 *
 * @package humhub.modules.post
 * @since 0.5
 */
?>
<div class="panel panel-default">
    <div class="panel-body">
        <?php $this->beginContent('application.modules_core.wall.views.wallLayout', array('object' => $post)); ?>
        <?php print HHtml::enrichText($post->message); ?>
        <?php $this->endContent(); ?>
    </div>
</div>
