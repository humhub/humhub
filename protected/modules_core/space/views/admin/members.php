<div class="panel panel-default">
    <div class="panel-heading">
        <?php echo Yii::t('SpaceModule.admin', 'Manage your members'); ?>
    </div>
    <div class="panel-body">

        <p><?php echo Yii::t('SpaceModule.admin', 'In the area below, you see all active members of this workspace.<br>You can edit and remove or invite new users.'); ?></p>

        <!-- user invite button -->
        <?php
        echo CHtml::link(Yii::t('SpaceModule.admin', 'Invite new user'), $this->createUrl('//space/space/invite', array('sguid' => $workspace->guid)), array('class' => 'btn btn-primary', 'data-toggle' => 'modal', 'data-target' => '#globalModal'));
        ?>

        <hr>
        <?php if (count($workspace->applicants) != 0) : ?>
            <div class="well well-small">
                <p><strong><?php echo Yii::t('SpaceModule.admin', 'Approval pending:'); ?></strong></p>
                <table width="100%" border="0">
                    <?php foreach ($workspace->applicants as $membership) : ?>
                        <?php $user = $membership->user; ?>
                        <tr>
                            <td width="100%">
                                <div class="media">
                                    <a class="pull-left" href="<?php echo $user->getProfileUrl(); ?>">

                                        <img class="media-object img-rounded"
                                             src="<?php echo $user->getProfileImage()->getUrl(); ?>" width="34"
                                             height="34" alt="34x34" data-src="holder.js/34x34"
                                             style="width: 34px; height: 34px;">
                                    </a>

                                    <div class="media-body">
                                        <h5 class="media-heading"><?php echo CHtml::link($user->displayName, $user->getProfileUrl()); ?></a>
                                            <?php if ($user->group != null) { ?>
                                                <small>
                                                    (<?php echo HHtml::link($user->group->name, $this->createUrl('community/members', array('keyword' => 'groupId:' . $user->group->id))); ?>
                                                    )
                                                </small>
                                            <?php } ?>
                                        </h5><br>
                                        <?php echo CHtml::encode($membership->request_message); ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php echo CHtml::link('<i class="icon-ok"></i>', $this->createUrl('//space/admin/adminMembersApproveApplicant', array('sguid' => $workspace->guid, 'userGuid' => $user->guid, 'approve' => true)), array('class' => "btn btn-xs btn-success tt", 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'data-original-title' => Yii::t('SpaceModule.base', 'Accept user'))); ?>
                                <?php echo CHtml::link('<i class="icon-remove"></i>', $this->createUrl('//space/admin/adminMembersRejectApplicant', array('sguid' => $workspace->guid, 'userGuid' => $user->guid, 'reject' => true)), array('class' => "btn btn-xs btn-danger tt", 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'data-original-title' => Yii::t('SpaceModule.base', 'Decline user'))); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            <hr>
        <?php endif; ?>

        <?php echo CHtml::form($this->createUrl('//space/admin/members', array('sguid' => $workspace->guid)), 'GET'); ?>
        <div class="row">
            <div class="col-md-9">
                <input type="text" class="form-control" name="search" value="<?php echo CHtml::encode($search); ?>"/>
            </div>
            <div class="col-md-3">
                <?php echo CHtml::submitButton("Search", array('class' => 'btn btn-primary')); ?>
            </div>
        </div>


        </form>
        <br>

        <?php echo Chtml::endForm(); ?>

        <?php echo CHtml::form($this->createUrl('//space/admin/members', array('sguid' => $workspace->guid)), 'post'); ?>
        <div class="well well-small">

            <?php if ($item_count > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th class="col-md-4 sortable">
                                <?php echo Yii::t('SpaceModule.admin', 'Name'); ?>
                            </th>
                            <th class="col-md-1">
                                <?php echo Yii::t('SpaceModule.admin', 'Can invite'); ?>
                            </th>
                            <th class="col-md-1">
                                <?php echo Yii::t('SpaceModule.admin', 'Can share'); ?>
                            </th>
                            <th class="col-md-1">
                                <?php echo Yii::t('SpaceModule.admin', 'Is admin'); ?>
                            </th>
                            <th class="col-md-1">

                            </th>
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

                            <!-- BEGIN: Results -->
                            <tr id="row_<?php echo $user->guid; ?>">
                                <td>
                                    <div class="media">
                                        <a class="pull-left" href="<?php echo $user->getProfileUrl(); ?>">

                                            <img class="media-object img-rounded"
                                                 src="<?php echo $user->getProfileImage()->getUrl(); ?>" width="48"
                                                 height="48" alt="48x48" data-src="holder.js/48x48"
                                                 style="width: 48px; height: 48px;">
                                        </a>

                                        <div class="media-body">
                                            <h5 class="media-heading"><?php echo CHtml::link($user->displayName, $user->getProfileUrl()); ?></a>
                                                <?php if ($user->group != null) { ?>
                                                    <small>
                                                        (<?php echo HHtml::link($user->group->name, $this->createUrl('community/members', array('keyword' => 'groupId:' . $user->group->id))); ?>
                                                        )
                                                    </small>
                                                <?php } ?>
                                            </h5>
                                            <?php echo $user->title; ?><br>
                                        </div>
                                    </div>
                                </td>

                                <?php if (!$workspace->isOwner($user->id)) : ?>
                                    <td><?php echo CHtml::checkBox('user_' . $user->guid . "[inviteRole]", $membership->invite_role, array('class' => 'check_invite', 'id' => "chk_invite_" . $user->id)); ?></td>
                                    <td><?php echo CHtml::checkBox('user_' . $user->guid . "[shareRole]", $membership->share_role, array('class' => 'check_share', 'id' => "chk_share_" . $user->id)); ?></td>
                                    <td><?php echo CHtml::checkBox('user_' . $user->guid . "[adminRole]", $membership->admin_role, array('class' => 'check_admin', 'id' => "chk_admin_" . $user->id)); ?></td>
                                    <td>
                                        <!-- load modal confirm widget -->
                                        <?php
                                        $this->widget('application.widgets.ModalConfirmWidget', array(
                                            'uniqueID' => $user->id,
                                            'message' => Yii::t('SpaceModule.admin', 'Are you sure, to remove this user from the space?'),
                                            'buttonTrue' => Yii::t('SpaceModule.admin', 'Yes, remove'),
                                            'buttonFalse' => Yii::t('SpaceModule.admin', 'No, cancel'),
                                            'class' => 'btn btn-mini btn-danger',
                                            'linkContent' => Yii::t('SpaceModule.admin', 'Remove'),
                                            'linkHref' => $this->createUrl('//space/admin/adminRemoveMember', array('guid' => $workspace->guid, 'userGuid' => $user->guid, 'ajax' => 1))
                                        ));
                                        ?>

                                    </td>
                                <?php else: ?>
                                    <td colspan="4"><div class="space-owner"><?php echo Yii::t('SpaceModule.admin', 'Space owner'); ?></div></td>
                                <?php endif; ?>
                            </tr>

                            <!-- END: Results -->

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
                        'nextPageLabel' => '<i class="icon-step-forward"></i>',
                        'prevPageLabel' => '<i class="icon-step-backward"></i>',
                        'firstPageLabel' => '<i class="icon-fast-backward"></i>',
                        'lastPageLabel' => '<i class="icon-fast-forward"></i>',
                        'header' => '',
                        'htmlOptions' => array('class' => 'pagination'),
                    ));
                    ?>
                </div>
            <?php else: ?>

                <strong><?php echo Yii::t('SpaceModule.admin', 'No users found!'); ?></strong>
                <br/>
                <br/>
            <?php endif; ?>




            <?php $owner = $workspace->getOwner(); ?>

            <?php if ($owner->id == Yii::app()->user->id): ?>
                <?php /* Change Owner of the workspace */ ?>
                <hr>
                <b>Owner of this workspace:</b>
                <select name="ownerId" class="form-control">
                    <?php foreach ($workspace->memberships as $membership) : ?>
                        <?php if ($membership->user == null) continue; ?>
                        <option
                            value="<?php echo $membership->user->id; ?>" <?php if ($membership->user->id == $owner->id): ?> selected <?php endif; ?>><?php echo $membership->user->displayName; ?></option>
                        <?php endforeach; ?>
                </select>
            <?php endif; ?>

        </div>
        <hr>
        <?php echo CHtml::submitButton("Save", array('class' => 'btn btn-primary')); ?>

        <!-- show flash message after saving -->
        <?php $this->widget('application.widgets.DataSavedWidget'); ?>

        <?php echo Chtml::endForm(); ?>

    </div>
</div>
