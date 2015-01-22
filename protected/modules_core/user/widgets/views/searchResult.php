<?php
/**
 * This View shows a user inside the search
 *
 * @property User $user is the user object
 *
 * @package humhub.modules_core.user
 * @since 0.5
 */
?>
<li>
    <a href="<?php echo $user->getUrl(); ?>">

        <div class="media">
            <img class="media-object img-rounded pull-left" src="<?php echo $user->getProfileImage()->getUrl(); ?>"
                 width="50"
                 height="50" alt="50x50" data-src="holder.js/50x50" style="width: 50px; height: 50px;">

            <div class="media-body">
                <h4 class="media-heading"><?php echo CHtml::encode($user->displayName); ?>
                    <?php if ($user->group != null) { ?>
                        <small>(<?php echo CHtml::encode($user->group->name); ?>)</small><?php } ?></h4>
                <?php if ($user->profile->title != "") { ?>
                    <h5><?php echo CHtml::encode($user->profile->title); ?></h5>
                <?php } ?>

                <?php $tag_count = 0; ?>
                <?php if ($user->tags) : ?>
                    <?php foreach ($user->getTags() as $tag): ?>
                        <?php if ($tag_count < 5) { ?>
                            <span class="label label-info"><?php echo $tag; ?></span>
                            <?php
                            $tag_count++;
                        }
                        ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </a>
</li>