<div class="container">
    <div class="row">
        <div class="col-md-12">
            <?php
            /** @var $space Space */
            ?>
            <div class="panel panel-default panel-profile">

                <div class="panel-profile-header">

                    <div class="image-upload-container" style="width: 100%; height: 100%; overflow:hidden;">
                        <!-- profile image output-->
                        <img class="img-profile-header-background" id="space-banner-image"
                             src="<?php echo $space->getProfileBannerImage()->getUrl(); ?>"
                             width="100%" style="width: 100%;">


                        <!-- show user name and title -->
                        <div class="img-profile-data">
                            <h1 class="space"><?php echo CHtml::encode($space->name); ?></h1>

                            <h2 class="space"><?php echo CHtml::encode($space->description); ?></h2>
                        </div>


                    </div>

                    <div class="image-upload-container profile-user-photo-container"
                         style="width: 140px; height: 140px;">

                        <?php

                        /* Get original profile image URL */

                        $profileImageExt = pathinfo($space->getProfileImage()->getUrl(), PATHINFO_EXTENSION);

                        $profileImageOrig = preg_replace('/.[^.]*$/', '', $space->getProfileImage()->getUrl());
                        $defaultImage = (basename($space->getProfileImage()->getUrl()) == 'default_user.jpg' || basename($space->getProfileImage()->getUrl()) == 'default_user.jpg?cacheId=0') ? true : false;
                        $profileImageOrig = $profileImageOrig . '_org.' . $profileImageExt;

                        if (!$defaultImage) {

                            ?>

                            <!-- profile image output-->
                            <a data-toggle="lightbox" data-gallery="" href="<?php echo $profileImageOrig; ?>#.jpeg"
                               data-footer='<button type="button" class="btn btn-primary" data-dismiss="modal"><?php echo Yii::t('FileModule.widgets_views_showFiles', 'Close'); ?></button>'>
                                <img class="img-rounded profile-user-photo" id="space-profile-image"
                                     src="<?php echo $space->getProfileImage()->getUrl(); ?>"
                                     data-src="holder.js/140x140" alt="140x140" style="width: 140px; height: 140px;"/>
                            </a>

                        <?php } else { ?>

                            <img class="img-rounded profile-user-photo" id="space-profile-image"
                                 src="<?php echo $space->getProfileImage()->getUrl(); ?>"
                                 data-src="holder.js/140x140" alt="140x140" style="width: 140px; height: 140px;"/>

                        <?php } ?>


                    </div>


                </div>

                <div class="panel-body">

                    <div class="panel-profile-controls">
                        <!-- start: User statistics -->
                        <div class="statistics pull-left">

                            <div class="pull-left entry">
                                <span class="count"><?php echo $space->countPosts(); ?></span></a>
                                <br>
                                <span
                                    class="title"><?php echo Yii::t('SpaceModule.widgets_views_profileHeader', 'Posts'); ?></span>
                            </div>

                            <div class="pull-left entry">
                                <span class="count"><?php echo count($space->memberships); ?></span>
                                <br>
                                <span
                                    class="title"><?php echo Yii::t('SpaceModule.widgets_views_profileHeader', 'Members'); ?></span>
                            </div>

                            <div class="pull-left entry">
                                <span class="count">?</span><br>
                                <span
                                    class="title"><?php echo Yii::t('SpaceModule.widgets_views_profileHeader', 'Followers'); ?></span>
                            </div>

                        </div>
                        <!-- end: User statistics -->


                        <div class="controls controls-account pull-right">
                            <?php
                            if (!Yii::app()->user->isGuest) {
                                // Membership Handling
                                if ($space->isMember(Yii::app()->user->id)) {
                                    if ($space->isSpaceOwner(Yii::app()->user->id)) {
                                        print Yii::t('SpaceModule.views_space_indexPublic', "You are the owner of this workspace.");
                                    } else {
                                        print '<br><br>';
                                        print CHtml::link(Yii::t('SpaceModule.views_space_indexPublic', "Cancel membership"), $this->createUrl('//space/space/revokeMembership', array('sguid' => $space->guid)), array('class' => 'btn btn-danger'));
                                    }
                                } else {
                                    $membership = $space->getMembership();
                                    if ($membership == null) {
                                        if ($space->canJoin()) {
                                            if ($space->join_policy == Space::JOIN_POLICY_APPLICATION) {
                                                echo CHtml::link(Yii::t('SpaceModule.views_space_indexPublic', 'Request membership'), $this->createUrl('//space/space/requestMembershipForm', array('sguid' => $space->guid)), array('class' => 'btn btn-primary', 'data-toggle' => 'modal', 'data-target' => '#globalModal'));
                                            } else {
                                                ?>
                                                <a href="<?php echo $this->createUrl('//space/space/requestMembership', array('sguid' => $space->guid)); ?>"
                                                   class="btn btn-primary"><?php echo Yii::t('SpaceModule.views_space_indexPublic', 'Become member'); ?></a>
                                            <?php
                                            }
                                        }
                                    } elseif ($membership->status == SpaceMembership::STATUS_INVITED) {
                                        print '<a href="' . Yii::app()->createUrl("//space/space/inviteAccept", array('sguid' => $space->guid)) . '" class="btn btn-primary">' . Yii::t('SpaceModule.views_space_indexPublic', 'Accept Invite') . '</a> ';
                                        print '<a href="' . Yii::app()->createUrl("//space/space/revokeMembership", array('sguid' => $space->guid)) . '" class="btn btn-primary">' . Yii::t('SpaceModule.views_space_indexPublic', 'Deny Invite') . '</a> ';
                                    } elseif ($membership->status == SpaceMembership::STATUS_APPLICANT) {
                                        print '<a href="' . Yii::app()->createUrl("//space/space/revokeMembership", array('sguid' => $space->guid)) . '" class="btn btn-primary" id="membership_button">' . Yii::t('SpaceModule.views_space_indexPublic', 'Cancel pending membership application') . '</a>';
                                    }
                                }
                            }
                            ?>

                            <?php
                            // Follow Handling
                            if (!Yii::app()->user->isGuest && !$space->isMember()) {
                                if ($space->isFollowedByUser()) {
                                    print HHtml::postLink(Yii::t('SpaceModule.views_space_indexPublic', "Unfollow"), $space->createUrl('//space/space/unfollow'), array('class' => 'btn btn-danger'));
                                } else {
                                    print HHtml::postLink(Yii::t('SpaceModule.views_space_indexPublic', "Follow"), $space->createUrl('//space/space/follow'), array('class' => 'btn btn-success'));
                                }
                            }
                            ?>

                            <?php if (Yii::app()->user->isGuest && $space->visibility != Space::VISIBILITY_ALL): ?>
                                <p><?php echo Yii::t('SpaceModule.views_space_indexPublic', "You need to login to view contents of this space!"); ?></p>
                            <?php endif; ?>
                        </div>

                    </div>


                </div>

            </div>

        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <?php $this->widget('application.modules_core.wall.widgets.WallStreamWidget', array('contentContainer' => $space)); ?>
        </div>
    </div>
</div>