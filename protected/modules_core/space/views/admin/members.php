<?php

/**@var $this AdminController */
/**@var $space Space */

?>

<div class="panel panel-default">
<div class="panel-heading">
    <?php echo Yii::t('SpaceModule.views_admin_members', '<strong>Manage</strong> your space members'); ?>
</div>
<div class="panel-body">

    <?php echo Yii::t('SpaceModule.views_admin_members', 'In the area below, you see all active members of this space. You can edit their privileges or remove it from this space.'); ?>


    <br/><br/>
    <?php echo CHtml::form($this->createUrl('//space/admin/members', array('sguid' => $space->guid)), 'GET'); ?>
    <div class="row">
        <div class="col-md-3"></div>
        <div class="col-md-6">
            <div class="form-group form-group-search">


                <input type="text" class="form-control form-search" placeholder="<?php echo Yii::t('SpaceModule.views_admin_members', "Search members"); ?>" name="search"
                       value="<?php echo CHtml::encode($search); ?>"/>
                <br/>
                <?php echo CHtml::submitButton(Yii::t('SpaceModule.views_admin_members', "Search"), array('class' => 'btn btn-default btn-sm form-button-search')); ?>

            </div>

        </div>

        <div class="col-md-3"></div>

    </div>
    <?php echo Chtml::endForm(); ?>

    <?php echo CHtml::form($this->createUrl('//space/admin/members', array('sguid' => $space->guid)), 'post'); ?>
    <?php if ($item_count > 0): ?>

        <table class="table table-hover">
            <thead>
            <tr>
                <th><?php echo Yii::t('SpaceModule.views_admin_members', "User"); ?></th>
                <th></th>
                <th><?php echo Yii::t('SpaceModule.views_admin_members', "Can invite"); ?> <i class="fa fa-info-circle tt" data-toggle="tooltip" data-placement="top"
                                  title="<?php echo Yii::t('SpaceModule.views_admin_members', 'Allow this user to<br>invite other users'); ?>"></i>
                </th>
                <th><?php echo Yii::t('SpaceModule.views_admin_members', "Can share"); ?> <i class="fa fa-info-circle tt" data-toggle="tooltip" data-placement="top"
                                 title="<?php echo Yii::t('SpaceModule.views_admin_members', 'Allow this user to<br>make content public'); ?>"></i>
                </th>
                <th><?php echo Yii::t('SpaceModule.views_admin_members', "Is admin"); ?> <i class="fa fa-info-circle tt" data-toggle="tooltip" data-placement="top"
                                title="<?php echo Yii::t('SpaceModule.views_admin_members', 'Make this user an admin'); ?>"></i>
                </th>
                <th></th>
            </tr>
            </thead>
            <tbody>

            <?php foreach ($members as $membership) : ?>
                <?php
                $user = $membership->user;
                if ($user == null)
                    continue;

                // Hidden field to get users on this page
                echo CHtml::hiddenField("users[" . $user->guid . "]", $user->guid);

                // Hidden field to get users on this page
                echo CHtml::hiddenField('user_' . $user->guid . "[placeholder]", 1);
                ?>

                <tr>
                    <td width="52px">
                        <a href="<?php echo $user->getProfileUrl(); ?>">

                            <img class="media-object img-rounded"
                                 src="<?php echo $user->getProfileImage()->getUrl(); ?>" width="48"
                                 height="48" alt="48x48" data-src="holder.js/48x48"
                                 style="width: 48px; height: 48px;">
                        </a>

                    </td>
                    <td>
                        <strong><?php echo CHtml::link($user->displayName, $user->getProfileUrl()); ?></strong>
                        <br/>
                        <?php echo CHtml::encode($user->profile->title); ?>

                    </td>

                    <?php if (!$space->isSpaceOwner($user->id)) : ?>
                        <td style="vertical-align:middle">
                            <div class="checkbox">
                                <label>
                                    <?php echo CHtml::checkBox('user_' . $user->guid . "[inviteRole]", $membership->invite_role, array('class' => 'check_invite', 'id' => "chk_invite_" . $user->id, 'data-view' => 'slider')); ?>
                                </label>
                            </div>
                        </td>
                        <td style="vertical-align:middle">
                            <div class="checkbox">
                                <label>
                                    <?php echo CHtml::checkBox('user_' . $user->guid . "[shareRole]", $membership->share_role, array('class' => 'check_share', 'id' => "chk_share_" . $user->id, 'data-view' => 'slider')); ?>
                                </label>
                            </div>
                        </td>
                        <td style="vertical-align:middle">
                            <div class="checkbox">
                                <label>
                                    <?php echo CHtml::checkBox('user_' . $user->guid . "[adminRole]", $membership->admin_role, array('class' => 'check_admin', 'id' => "chk_admin_" . $user->id, 'data-view' => 'slider')); ?>
                                </label>
                            </div>
                        </td>




                        <td style="vertical-align:middle">
                            <!-- load modal confirm widget -->
                            <?php
                            $this->widget('application.widgets.ModalConfirmWidget', array(
                                'uniqueID' => $user->id,
                                'title' => Yii::t('SpaceModule.views_admin_members', '<strong>Remove</strong> member'),
                                'message' => Yii::t('SpaceModule.views_admin_members', 'Are you sure, that you want to remove this member from this space?'),
                                'buttonTrue' => Yii::t('SpaceModule.views_admin_members', 'Yes, remove'),
                                'buttonFalse' => Yii::t('SpaceModule.views_admin_members', 'No, cancel'),
                                'class' => 'btn btn-sm btn-danger',
                                'linkContent' => Yii::t('SpaceModule.views_admin_members', 'Remove'),
                                'linkHref' => $this->createUrl('//space/admin/removeMember', array('sguid' => $space->guid, 'userGuid' => $user->guid, 'ajax' => 1))
                            ));
                            ?>

                        </td>
                    <?php else: ?>
                        <td colspan="3">
                            <div class="space-owner"><?php echo Yii::t('SpaceModule.views_admin_members', 'Space owner'); ?></div>
                        </td>
                        <td></td>
                    <?php endif; ?>

                </tr>

            <?php endforeach; ?>

            </tbody>
        </table>

        <div class="pagination-container">
            <?php
            $this->widget('CLinkPager', array(
                'currentPage' => $pages->getCurrentPage(),
                'itemCount' => $item_count,
                'pageSize' => $page_size,
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

    <?php endif; ?>




    <?php $owner = $space->getSpaceOwner(); ?>

    <?php if ($space->isSpaceOwner()): ?>
        <p>
            <a data-toggle="collapse" id="space-owner-link" href="#collapse-space-owner" style="font-size: 11px;"><i
                    class="fa fa-caret-right"></i> <?php echo Yii::t('SpaceModule.views_admin_members', 'Change space owner') ?>
            </a>
        </p>
        <div id="collapse-space-owner" class="panel-collapse collapse">
            <div class="well well-sm">

                <p>    <?php echo Yii::t('SpaceModule.views_admin_members', 'The space owner is the super admin of a space with all privileges and normally the creator of the space. Here you can change this role to another user.') ?></p>

                <div class="row">
                    <div class="col-md-5">

                        <select name="ownerId" class="form-control">
                            <?php foreach ($space->memberships as $membership) : ?>
                                <?php if ($membership->user == null) continue; ?>
                                <option
                                    value="<?php echo $membership->user->id; ?>" <?php if ($space->isSpaceOwner($membership->user->id)): ?> selected <?php endif; ?>><?php echo CHtml::encode($membership->user->displayName); ?></option>
                            <?php endforeach; ?>
                        </select>

                    </div>
                    <div class="col-md-7"></div>
                </div>
            </div>

        </div>

    <?php endif; ?>


    <hr>
    <?php echo CHtml::submitButton(Yii::t('SpaceModule.views_admin_members', "Save"), array('class' => 'btn btn-primary')); ?>

    <!-- show flash message after saving -->
    <?php $this->widget('application.widgets.DataSavedWidget'); ?>

    <?php echo Chtml::endForm(); ?>

</div>
</div>
<?php if (count($space->applicants) != 0) : ?>
    <div class="panel panel-danger">
        <div class="panel-heading">
            <?php echo Yii::t('SpaceModule.views_admin_members', '<strong>Outstanding</strong> user requests'); ?>
        </div>
        <div class="panel-body">
            <p>
                <?php echo Yii::t('SpaceModule.views_admin_members', "The following users waiting for an approval to enter this space. Please take some action now."); ?>
            </p>


            <table class="table table-hover">
                <thead>
                <tr>
                    <th><?php echo Yii::t('SpaceModule.views_admin_members', "User"); ?></th>
                    <th></th>
                    <th><?php echo Yii::t('SpaceModule.views_admin_members', "Request message"); ?></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($space->applicants as $membership) : ?>
                    <?php $user = $membership->user; ?>
                    <tr>
                        <td width="40px">
                            <a href="<?php echo $user->getProfileUrl(); ?>">

                                <img class="media-object img-rounded"
                                     src="<?php echo $user->getProfileImage()->getUrl(); ?>" width="34"
                                     height="34" alt="34x34" data-src="holder.js/34x34"
                                     style="width: 34px; height: 34px;">
                            </a>
                        </td>
                        <td>
                            <strong><?php echo CHtml::link($user->displayName, $user->getProfileUrl()); ?></strong>
                            <br/>
                            <?php echo CHtml::encode($user->profile->title); ?>
                        </td>
                        <td>
                            <?php echo CHtml::encode($membership->request_message); ?>
                        </td>
                        <td width="150px">
                            <?php echo HHtml::postLink(Yii::t('SpaceModule.views_admin_members', 'Accept'), $this->createUrl('//space/admin/membersApproveApplicant', array('sguid' => $space->guid, 'userGuid' => $user->guid, 'approve' => true)), array('class' => "btn btn-sm btn-success")); ?>
                            <?php echo HHtml::postLink(Yii::t('SpaceModule.views_admin_members', 'Decline'), $this->createUrl('//space/admin/membersRejectApplicant', array('sguid' => $space->guid, 'userGuid' => $user->guid, 'reject' => true)), array('class' => "btn btn-sm btn-danger")); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                </tbody>
            </table>
        </div>
    </div>

<?php endif; ?>

<?php if (count($invited_members) != 0) : ?>

<div class="panel panel-success">
    <div class="panel-heading">
        <?php echo Yii::t('SpaceModule.views_admin_members', '<strong>Outstanding</strong> sent invitations'); ?>
    </div>
    <div class="panel-body">
        <p>
            <?php echo Yii::t('SpaceModule.views_admin_members', "The following users were already invited to this space, but haven't accepted the invitation yet."); ?>
            <?php if (HSetting::Get('internalUsersCanInvite', 'authentication_internal')) : ?>
                <br/>
                <?php echo Yii::t('SpaceModule.views_admin_members', "External users who invited by email, will be not listed here."); ?>
            <?php endif; ?>
        </p>

        <table class="table table-hover">
            <thead>
            <tr>
                <th><?php echo Yii::t('SpaceModule.views_admin_members', "User"); ?></th>
                <th></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($invited_members as $membership) : ?>
                <?php $user = $membership->user; ?>

                <tr>
                    <td width="40px">
                        <a href="<?php echo $user->getProfileUrl(); ?>">

                            <img class="media-object img-rounded"
                                 src="<?php echo $user->getProfileImage()->getUrl(); ?>" width="34"
                                 height="34" alt="34x34" data-src="holder.js/34x34"
                                 style="width: 34px; height: 34px;">
                        </a>
                    </td>
                    <td>
                        <strong><?php echo CHtml::link($user->displayName, $user->getProfileUrl()); ?></strong>
                        <br/>
                        <?php echo CHtml::encode($user->profile->title); ?>
                    </td>
                    <td width="100px">
                        <?php echo HHtml::postLink(Yii::t('SpaceModule.views_admin_members', 'Revoke invitation'), $this->createUrl('//space/admin/membersRejectApplicant', array('sguid' => $space->guid, 'userGuid' => $user->guid, 'reject' => true)), array('class' => "btn btn-sm btn-primary")); ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<script type="text/javascript">

    $('#collapse-space-owner').on('show.bs.collapse', function () {
        // change link arrow
        $('#space-owner-link i').removeClass('fa-caret-right');
        $('#space-owner-link i').addClass('fa-caret-down');
    })

    $('#collapse-space-owner').on('hide.bs.collapse', function () {
        // change link arrow
        $('#space-owner-link i').removeClass('fa-caret-down');
        $('#space-owner-link i').addClass('fa-caret-right');
    })




</script>
