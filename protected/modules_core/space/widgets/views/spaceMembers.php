<?php if ($space->isAdmin() && count($space->applicants) != 0) : ?>
    <div class="panel panel-danger">

        <div class="panel-heading"><?php echo Yii::t('SpaceModule.base', '<strong>New</strong> member request'); ?></div>

        <div class="panel-body">
            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                <?php $user_count = 0; ?>
                <?php foreach ($space->applicants as $membership) : ?>
                    <?php $user = $membership->user; ?>
                    <?php if ($user == null) continue; ?>
                    <tr>
                        <td align="left" valign="top" width="30">


                            <a href="<?php echo $user->profileUrl; ?>" alt="<?php echo $user->displayName ?>">
                                <img class="img-rounded tt img_margin"
                                     src="<?php echo $user->getProfileImage()->getUrl(); ?>" height="24" width="24"
                                     alt="24x24" data-src="holder.js/24x24" style="width: 24px; height: 24px;"
                                     data-toggle="tooltip" data-placement="top" title=""
                                     data-original-title="<strong><?php echo $user->displayName; ?></strong><br><?php echo $user->profile->title; ?>"/>
                            </a>


                        </td>

                        <td align="left" valign="top">
                            <strong><?php echo $user->displayName ?></strong><br>
                            <?php echo CHtml::encode($membership->request_message); ?><br>

                            <hr>
                            <?php echo CHtml::link('Accept', $this->createUrl('//space/admin/adminMembersApproveApplicant', array('sguid' => $space->guid, 'userGuid' => $user->guid, 'approve' => true)), array('class' => 'btn btn-success btn-sm', 'id' => 'user_accept_' . $user->guid)); ?>
                            <?php echo CHtml::link('Decline', $this->createUrl('//space/admin/adminMembersRejectApplicant', array('sguid' => $space->guid, 'userGuid' => $user->guid, 'reject' => true)), array('class' => 'btn btn-danger btn-sm', 'id' => 'user_decline_' . $user->guid)); ?>

                        </td>
                    </tr>
                    <?php if ($user_count < count($space->applicants) - 1) { ?>
                        <hr>
                    <?php } ?>
                    <?php
                    $user_count++;
                endforeach;
                ?>
            </table>
        </div>

    </div>
<?php endif; ?>

<div class="panel panel-default members" id="space-members-panel">

    <!-- Display panel menu widget -->
    <?php $this->widget('application.widgets.PanelMenuWidget', array('id' => 'space-members-panel')); ?>

    <div class="panel-heading"><?php echo Yii::t('SpaceModule.base', '<strong>Space</strong> members'); ?></div>
    <div class="panel-body">
        <?php if (count($space->membershipsLimited) != 0) : ?>
            <?php $ix = 0; ?>
            <?php foreach ($space->membershipsLimited as $membership) : ?>
                <?php $user = User::model()->findByPk($membership->user_id); ?>
                <?php if ($user == null || $user->status != User::STATUS_ENABLED) continue; ?>
                <?php if ($ix > 23) break; ?>
                <?php $ix++; ?>
                <a href="<?php echo $user->getProfileUrl(); ?>">
                    <img src="<?php echo $user->getProfileImage()->getUrl(); ?>" class="img-rounded tt img_margin"
                         height="24" width="24" alt="24x24" data-src="holder.js/24x24"
                         style="width: 24px; height: 24px;" data-toggle="tooltip" data-placement="top" title=""
                         data-original-title="<strong><?php echo $user->displayName; ?></strong><br><?php echo $user->profile->title; ?>">
                </a>
                <?php if ($space->isAdmin($user->id)) { ?>
                    <!-- output, if user is admin of this space -->
                <?php } ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <hr>

        <?php if ($space->canInvite()) { ?>

            <!-- user invite button -->
            <?php
            echo CHtml::link(Yii::t('SpaceModule.base', 'Invite'), $this->createUrl('//space/space/invite', array('sguid' => $space->guid)), array('class' => 'btn btn-primary', 'data-toggle' => 'modal', 'data-target' => '#globalModal'));
            ?>

        <?php } ?>

        <?php
        // Membership Handling
        if ($space->isMember(Yii::app()->user->id)) {
            if (!$space->isOwner(Yii::app()->user->id)) {
                print CHtml::link('<i class="fa fa-sign-out"></i> '. Yii::t('SpaceModule.base', "Leave space"), $this->createUrl('//space/space/revokeMembership', array('sguid' => $space->guid)), array('class' => 'btn btn-danger'));
            }
        } else {
            $membership = $space->getMembership();
            if ($membership == null) {
                if ($space->canJoin()) {
                    if ($space->join_policy == Space::JOIN_POLICY_APPLICATION) {
                        echo CHtml::link(Yii::t('SpaceModule.base', 'Request membership'), $this->createUrl('//space/space/requestMembershipForm', array('sguid' => $space->guid)), array('class' => 'btn btn-primary', 'data-toggle' => 'modal', 'data-target' => '#globalModal'));
                    } else {
                        ?>
                        <a href="<?php echo $this->createUrl('//space/space/requestMembership', array('sguid' => $space->guid)); ?>"
                           class="btn btn-primary"><?php echo Yii::t('SpaceModule.base', 'Become member'); ?></a>
                    <?php
                    }
                }
            } elseif ($membership->status == SpaceMembership::STATUS_INVITED) {
                print '<a href="' . Yii::app()->createUrl("//space/space/inviteAccept", array('sguid' => $space->guid)) . '" class="btn btn-primary">' . Yii::t('SpaceModule.base', 'Accept invite') . '</a>';
                print '<a href="' . Yii::app()->createUrl("//space/space/revokeMembership", array('sguid' => $space->guid)) . '" class="btn btn-primary">' . Yii::t('SpaceModule.base', 'Deny invite') . '</a>';
            } elseif ($membership->status == SpaceMembership::STATUS_APPLICANT) {
                print '<a href="' . Yii::app()->createUrl("//space/space/revokeMembership", array('sguid' => $space->guid)) . '" class="btn btn-primary" id="membership_button">' . Yii::t('SpaceModule.base', 'Cancel pending membership application') . '</a>';
            }
        }
        ?>

        <?php
        // Follow Handling
        if (!$space->isMember()) {
            if ($space->isFollowedBy()) {
                print CHtml::link(Yii::t('base', "Unfollow"), $this->createUrl('//space/space/unfollow', array('sguid' => $space->guid)), array('class' => 'btn btn-primary'));
            } else {
                print CHtml::link(Yii::t('base', "Follow"), $this->createUrl('//space/space/follow', array('sguid' => $space->guid)), array('class' => 'btn btn-primary'));
            }
        }
        ?>
    </div>
</div>