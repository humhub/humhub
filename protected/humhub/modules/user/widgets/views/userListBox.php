<?php

use yii\helpers\Html;
?>

<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"
                id="myModalLabel">
                    <?php echo $title; ?>
            </h4>
            <br/>
        </div>

        <?php if (count($users) === 0): ?>
            <div class="modal-body">
                <p><?php echo Yii::t('UserModule.base', 'No users found.'); ?></p>
            </div>
        <?php endif; ?>      


        <div id="userlist-content">

            <ul class="media-list">
                <!-- BEGIN: Results -->
                <?php foreach ($users as $user) : ?>
                    <li>
                        <a href="<?php echo $user->getUrl(); ?>">

                            <div class="media">
                                <img class="media-object img-rounded pull-left"
                                     src="<?php echo $user->getProfileImage()->getUrl(); ?>" width="50"
                                     height="50" alt="50x50" data-src="holder.js/50x50"
                                     style="width: 50px; height: 50px;">

                                <div class="media-body">
                                    <h4 class="media-heading"><?php echo Html::encode($user->displayName); ?></h4>
                                    <h5><?php echo Html::encode($user->profile->title); ?></h5>
                                </div>
                            </div>
                        </a>
                    </li>


                <?php endforeach; ?>
                <!-- END: Results -->

            </ul>

            <div class="pagination-container">
                <?= \humhub\widgets\AjaxLinkPager::widget(['pagination' => $pagination]); ?>
            </div>


        </div>


    </div>

</div>

<script type="text/javascript">

    // scroll to top of list
    $(".modal-body").animate({scrollTop: 0}, 200);

</script>

