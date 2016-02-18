<?php

use yii\helpers\Url;
use yii\helpers\Html;
?>
<div class="panel panel-default">

    <div class="panel-heading">
        <?php if ($group === null) : ?>
            <?php echo Yii::t('DirectoryModule.views_directory_members', '<strong>Member</strong> directory'); ?>
        <?php else: ?>
            <?php echo Yii::t('DirectoryModule.views_directory_members', '<strong>Group</strong> members - {group}', ['{group}' => $group->name]); ?>
        <?php endif; ?>
    </div>

    <div class="panel-body">

        <!-- search form -->
        <?php echo Html::beginForm(Url::to(['/directory/directory/members']), 'get', array('class' => 'form-search')); ?>
        <div class="row">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                <div class="form-group form-group-search">
                    <?php echo Html::textInput("keyword", $keyword, array("class" => "form-control form-search", "placeholder" => Yii::t('DirectoryModule.views_directory_members', 'search for members'))); ?>
                    <?php echo Html::submitButton(Yii::t('DirectoryModule.views_directory_members', 'Search'), array('class' => 'btn btn-default btn-sm form-button-search')); ?>
                </div>
            </div>
            <div class="col-md-3"></div>
        </div>
        <?php echo Html::endForm(); ?>

        <?php if (count($users) == 0): ?>
            <p><?php echo Yii::t('DirectoryModule.views_directory_members', 'No members found!'); ?></p>
        <?php endif; ?>

    </div>
    <hr>

    <ul class="media-list">
        <!-- BEGIN: Results -->
        <?php foreach ($users as $user) : ?>
            <li>
                <div class="media">

                    <!-- Follow Handling -->
                    <div class="pull-right">
                        <?php
                        if (!Yii::$app->user->isGuest && !(Yii::$app->user->id === $user->id)) {
                            $followed = $user->isFollowedByUser();
                            echo Html::a(Yii::t('DirectoryModule.views_directory_members', 'Follow'), 'javascript:setFollow("' . Url::to(['/user/profile/follow']) . '", "' . $user->id . '")', array('class' => 'btn btn-info btn-sm ' . (($followed) ? 'hide' : ''), 'id' => 'button_follow_' . $user->id));
                            echo Html::a(Yii::t('DirectoryModule.views_directory_members', 'Unfollow'), 'javascript:setUnfollow("' . Url::to(['/user/profile/unfollow']) . '", "' . $user->id . '")', array('class' => 'btn btn-primary btn-sm ' . (($followed) ? '' : 'hide'), 'id' => 'button_unfollow_' . $user->id));
                        }
                        ?>
                    </div>

                    <a href="<?php echo $user->getUrl(); ?>" class="pull-left">
                        <img class="media-object img-rounded"
                             src="<?php echo $user->getProfileImage()->getUrl(); ?>" width="50"
                             height="50" alt="50x50" data-src="holder.js/50x50"
                             style="width: 50px; height: 50px;">
                    </a>


                    <div class="media-body">
                        <h4 class="media-heading"><a
                                href="<?php echo $user->getUrl(); ?>"><?php echo Html::encode($user->displayName); ?></a>
                                <?php if ($user->group != null) { ?>
                                <small>(<?php echo Html::encode($user->group->name); ?>)</small><?php } ?>
                        </h4>
                        <h5><?php echo Html::encode($user->profile->title); ?></h5>

                        <?php $tag_count = 0; ?>
                        <?php if ($user->hasTags()) : ?>
                            <?php foreach ($user->getTags() as $tag): ?>
                                <?php if ($tag_count <= 5) { ?>
                                    <?php echo Html::a(Html::encode($tag), Url::to(['/directory/directory/members', 'keyword' => $tag]), array('class' => 'label label-default')); ?>
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
    <?php echo \humhub\widgets\LinkPager::widget(['pagination' => $pagination]); ?>
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
