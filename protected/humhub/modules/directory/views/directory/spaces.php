<?php

use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="panel panel-default">

    <div class="panel-heading">
        <?php echo Yii::t('DirectoryModule.views_directory_spaces', '<strong>Space</strong> directory'); ?>
    </div>

    <div class="panel-body">

        <?php echo Html::beginForm(Url::to(['/directory/directory/spaces']), 'get', array('class' => 'form-search')); ?>
        <div class="row">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                <div class="form-group form-group-search">
                    <?php echo Html::textInput("keyword", $keyword, array("class" => "form-control form-search", "placeholder" => Yii::t('DirectoryModule.views_directory_spaces', 'search for spaces'))); ?>
                    <?php echo Html::submitButton(Yii::t('DirectoryModule.views_directory_spaces', 'Search'), array('class' => 'btn btn-default btn-sm form-button-search')); ?>
                </div>
            </div>
            <div class="col-md-3"></div>
        </div>
        <?php echo Html::endForm(); ?>


        <?php if (count($spaces) == 0): ?>
            <p><?php echo Yii::t('DirectoryModule.views_directory_spaces', 'No spaces found!'); ?></p>
        <?php endif; ?>

    </div>

    <hr>
    <ul class="media-list">

        <!-- BEGIN: Results -->
        <?php foreach ($spaces as $space) : ?>
            <li>
                <div class="media">

                    <!-- Follow Handling -->
                    <div class="pull-right">
                        <?php
                        if (!Yii::$app->user->isGuest && !$space->isMember()) {
                            $followed = $space->isFollowedByUser();
                            echo Html::a(Yii::t('DirectoryModule.views_directory_members', 'Follow'), 'javascript:setFollow("' . $space->createUrl('/space/space/follow') . '", "' . $space->id . '")', array('class' => 'btn btn-success btn-sm ' . (($followed) ? 'hide' : ''), 'id' => 'button_follow_' . $space->id));
                            echo Html::a(Yii::t('DirectoryModule.views_directory_members', 'Unfollow'), 'javascript:setUnfollow("' . $space->createUrl('/space/space/unfollow') . '", "' . $space->id . '")', array('class' => 'btn btn-primary btn-sm ' . (($followed) ? '' : 'hide'), 'id' => 'button_unfollow_' . $space->id));
                        }
                        ?>                        
                    </div>

                    <a href="<?php echo $space->getUrl(); ?>" class="pull-left">
                        <img class="media-object img-rounded"
                             src="<?php echo $space->getProfileImage()->getUrl(); ?>" width="50"
                             height="50" alt="50x50" data-src="holder.js/50x50" style="width: 50px; height: 50px;">
                    </a>

                    <?php if ($space->isMember()) { ?>
                        <i class="fa fa-user space-member-sign tt" data-toggle="tooltip" data-placement="top"
                           title=""
                           data-original-title="<?php echo Yii::t('DirectoryModule.views_directory_spaces', 'You are a member of this space'); ?>"></i>
                       <?php } ?>

                    <div class="media-body">
                        <h4 class="media-heading"><a href="<?php echo $space->getUrl(); ?>"><?php echo Html::encode($space->name); ?></a></h4>
                        <h5><?php echo Html::encode(humhub\libs\Helpers::truncateText($space->description, 100)); ?></h5>

                        <?php $tag_count = 0; ?>
                        <?php if ($space->tags) : ?>
                            <?php foreach ($space->getTags() as $tag): ?>
                                <?php if ($tag_count <= 5) { ?>
                                    <?php echo Html::a($tag, ['/directory/directory/spaces', 'keyword' => 'tags:' . $tag], array('class' => 'label label-default')); ?>
                                    <?php
                                    $tag_count++;
                                }
                                ?>
                            <?php endforeach; ?>
                        <?php endif; ?>

                    </div>
                </div>
            </li>
        <?php endforeach; ?>

        <!-- END: Results -->
    </ul>
</div>

<div class="pagination-container">
    <?php echo \humhub\widgets\LinkPager::widget(array('pagination' => $pagination)); ?>
</div>

<script type="text/javascript">
    // ajax request to follow the user
    function setFollow(url, id) {
        jQuery.ajax({
            url: url,
            type: "POST",
            'success': function () {
                $("#button_follow_" + id).addClass('hide');
                $("#button_unfollow_" + id).removeClass('hide');
            }});
    }

    // ajax request to unfollow the user
    function setUnfollow(url, id) {
        jQuery.ajax({
            url: url,
            type: "POST",
            'success': function () {
                $("#button_follow_" + id).removeClass('hide');
                $("#button_unfollow_" + id).addClass('hide');
            }});
    }

</script>
