<?php
/**
 * View to display a box with users and optional pagination.
 * This view is used by serveral modules to display a set of users.
 *
 * @property String $title is the title of the box.
 * @property CPagination $pagination is the pagination object.
 * @property Array $users is the arary of users to display.
 *
 * @package humhub.modules_core.user.views
 * @since 0.5
 */
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


        <div id="userlist-content">
            <ul class="media-list">
                <!-- BEGIN: Results -->
                <?php foreach ($users as $user) : ?>
                    <?php
                    // Check for null user, if there are "zombies" in search index
                    if ($user == null)
                        continue;
                    ?>
                    <li>
                        <a href="<?php echo $user->getUrl(); ?>">

                            <div class="media">
                                <img class="media-object img-rounded pull-left"
                                     src="<?php echo $user->getProfileImage()->getUrl(); ?>" width="50"
                                     height="50" alt="50x50" data-src="holder.js/50x50"
                                     style="width: 50px; height: 50px;">


                                <div class="media-body">
                                    <h4 class="media-heading"><?php echo CHtml::encode($user->displayName); ?>
                                        <?php if ($user->group != null) { ?>
                                            <small>(<?php echo CHtml::encode($user->group->name); ?>)</small><?php } ?>
                                    </h4>
                                    <h5><?php echo CHtml::encode($user->profile->title); ?></h5>
                                </div>
                            </div>
                        </a>
                    </li>


                <?php endforeach; ?>
                <!-- END: Results -->

            </ul>

            <div class="pagination-container">
                <?php
                $this->widget('HAjaxLinkPager', array(
                    'currentPage' => $pagination->getCurrentPage(),
                    'itemCount' => $pagination->getItemCount(),
                    'pageSize' => HSetting::Get('paginationSize'),
                    'maxButtonCount' => 5,
                    'ajaxContentTarget' => '.modal-dialog',
                    'nextPageLabel' => '<i class="fa fa-step-forward"></i>',
                    'prevPageLabel' => '<i class="fa fa-step-backward"></i>',
                    'firstPageLabel' => '<i class="fa fa-fast-backward"></i>',
                    'lastPageLabel' => '<i class="fa fa-fast-forward"></i>',
                    'header' => '',
                    'htmlOptions' => array('class' => 'pagination'),
                ));
                ?>
            </div>


        </div>


    </div>

</div>

<script type="text/javascript">

    // scroll to top of list
    $(".modal-body").animate({ scrollTop: 0 }, 200);

</script>

