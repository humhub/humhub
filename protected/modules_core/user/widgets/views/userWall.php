<div class="panel panel-default" id="user-wall-<?php echo $user->id; ?>">
    <div class="panel-body">

        <div class="media">
            <a href="<?php echo $user->getProfileUrl(); ?>" class="pull-left">
                <img class="media-object img-rounded user-image user-<?php echo $user->guid; ?>" alt="40x40" data-src="holder.js/40x40" style="width: 40px; height: 40px;"
                     src="<?php echo $user->getProfileImage()->getUrl(); ?>"
                     width="40" height="40"/>
            </a>
            <div class="media-body">
                <!-- show username with link and creation time-->
                <h4 class="media-heading"><a href="<?php echo $user->getProfileUrl(); ?>"><?php echo CHtml::encode($user->displayName); ?></a> </h4>
                <h5><?php echo CHtml::encode($user->profile->title); ?></h5>
            </div>
        </div>

    </div>
</div>
