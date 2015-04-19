<?php
/**
 * Members page of directory
 *
 * @property String $keyword the search keyword.
 * @property Array $hits is a list of lucene search hits.
 * @property Integer $pages is the number of total pages available.
 * @property Integer $hitCount is the number of total results.
 * @property Integer $pageSize is the number of results per page.
 *
 * @package humhub.modules_core.directory.views
 * @since 0.5
 */
?>
<div class="panel panel-default">

    <div class="panel-heading">
        <?php echo Yii::t('DirectoryModule.views_directory_members', '<strong>Member</strong> directory'); ?>
    </div>

    <div class="panel-body">

        <!-- search form -->

        <?php echo CHtml::form(Yii::app()->createUrl('//directory/directory/members', array()), 'post', array('class' => 'form-search')); ?>
        <div class="row">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                <div class="form-group form-group-search">
                    <?php echo CHtml::textField("keyword", $keyword, array("class" => "form-control form-search", "placeholder" => Yii::t('DirectoryModule.views_directory_members', 'search for members'))); ?>
                    <?php echo CHtml::submitButton(Yii::t('DirectoryModule.views_directory_members', 'Search'), array('class' => 'btn btn-default btn-sm form-button-search')); ?>
                </div>
            </div>
            <div class="col-md-3"></div>
        </div>
        <?php echo CHtml::endForm(); ?>



        <?php if ($hitCount == 0): ?>
            <p><?php echo Yii::t('DirectoryModule.views_directory_members', 'No members found!'); ?></p>
        <?php endif; ?>

    </div>
    <hr>

    <ul class="media-list">
        <!-- BEGIN: Results -->
        <?php foreach ($hits as $hit) : ?>
            <?php
            $doc = $hit->getDocument();
            $model = $doc->getField("model")->value;
            $userId = $doc->getField('pk')->value;
            $user = User::model()->findByPk($userId);

            // Check for null user, if there are "zombies" in search index
            if ($user == null)
                continue;
            ?>
            <li>

                <div class="media">

                    <!-- Follow Handling -->
                    <div class="pull-right">
                        <?php
                        if (!Yii::app()->user->isGuest && !$user->isCurrentUser()) {
                            $followed = $user->isFollowedByUser();
                            echo HHtml::postLink(Yii::t('DirectoryModule.views_directory_members', 'Follow'), 'javascript:setFollow("' . $user->createUrl('//user/profile/follow') . '", "' . $user->id . '")', array('class' => 'btn btn-success btn-sm ' . (($followed) ? 'hide' : ''), 'id' => 'button_follow_' . $user->id));
                            echo HHtml::postLink(Yii::t('DirectoryModule.views_directory_members', 'Unfollow'), 'javascript:setUnfollow("' . $user->createUrl('//user/profile/unfollow') . '", "' . $user->id . '")', array('class' => 'btn btn-primary btn-sm ' . (($followed) ? '' : 'hide'), 'id' => 'button_unfollow_' . $user->id));
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
                                href="<?php echo $user->getUrl(); ?>"><?php echo CHtml::encode($user->displayName); ?></a>
                                <?php if ($user->group != null) { ?>
                                <small>(<?php echo CHtml::encode($user->group->name); ?>)</small><?php } ?>
                        </h4>
                        <h5><?php echo CHtml::encode($user->profile->title); ?></h5>

                        <?php $tag_count = 0; ?>
                        <?php if ($user->tags) : ?>
                            <?php foreach ($user->getTags() as $tag): ?>
                                <?php if ($tag_count <= 5) { ?>
                                    <?php echo HHtml::link($tag, $this->createUrl('//directory/directory/members', array('keyword' => 'tags:' . $tag)), array('class' => 'label label-default')); ?>
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
    <?php
    $this->widget('CLinkPager', array(
        'currentPage' => $pages->getCurrentPage(),
        'itemCount' => $hitCount,
        'pageSize' => $pageSize,
        'maxButtonCount' => 5,
        'nextPageLabel' => '<i class="fa fa-step-forward"></i>',
        'prevPageLabel' => '<i class="fa fa-step-backward"></i>',
        'firstPageLabel' => '<i class="fa fa-fast-backward"></i>',
        'lastPageLabel' => '<i class="fa fa-fast-forward"></i>',
        'header' => '',
        'htmlOptions' => array('class' => 'pagination'),
    ));
    ?>
</div>


<script type="text/javascript">

    // ajax request to follow the user
    function setFollow(url, id) {
        jQuery.ajax({
            url: url,
            type: "POST",
            'success': function() {
                $("#button_follow_" + id).addClass('hide');
                $("#button_unfollow_" + id).removeClass('hide');
            }});
    }

    // ajax request to unfollow the user
    function setUnfollow(url, id) {
        jQuery.ajax({
            url: url,
            type: "POST",
            'success': function() {
                $("#button_follow_" + id).removeClass('hide');
                $("#button_unfollow_" + id).addClass('hide');
            }});
    }

</script>
