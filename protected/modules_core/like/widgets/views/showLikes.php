<?php
/**
 * Show the likes.
 *
 * @property Array $likes the list of likes to display
 * @property Boolean $currentUserLiked indicates if the current user liked this.
 * @property String $modelName The Model (e.g. Post) which the comments belongs to
 * @property Int $modelId The Primary Key of the Model which the comments belongs to
 * @property String $id is a unique Id on Model and PK e.g. (Post_1)
 *
 * @package humhub.modules_core.like
 * @since 0.5
 */
?>
<div id="<?php echo $id; ?>-ShowLikes" class="comment">
    <!--Dir und <a href="#">10</a> weiteren gefällt das.-->

    Total Likes:  <?php echo count($likes); ?> -

    <?php If ($currentUserLiked) : ?>
        You likes it!
    <?php endif; ?>

</div>