<div class="container">
    <div class="row">
        <div class="panel panel-default">


            <div class="panel-body">
                <div class="media">
                    <a class="pull-left" href="#">
                        <img src="<?php echo $space->getProfileImage()->getUrl(); ?>" width="150" height="150"
                             class="img-rounded" alt="150x150" data-src="holder.js/150x150"
                             style="width: 150px; height: 150px;"/><br>
                    </a>

                    <div class="media-body">
                        <h3 class="media-heading"><?php echo $space->name; ?></h3>
                        <?php echo Yii::t('SpaceModule.views_space_indexPublic', 'created by'); ?> <a
                            href="<?php echo Yii::app()->createUrl('//user/profile', array('uguid' => $space->getSpaceOwner()->guid)); ?>"><?php echo $space->getSpaceOwner()->displayName; ?></a>
                        <?php if ($space->description != "") { ?>
                            <hr>
                            <?php echo $space->description; ?>
                        <?php } ?>
                        <br/>
                        <!-- start: space tags -->
                        <?php if(count($space->getTags()) > 1 ) : ?>
                        <div class="tags">
                            <?php foreach ($space->getTags() as $tag) { ?>
                                <?php echo HHtml::link($tag, $this->createUrl('//directory/directory/spaces', array('keyword' => 'tags:' . $tag, 'areas' => array('Space'))), array('class' => 'btn btn-default btn-xs tag')); ?>
                            <?php } ?>
                        </div>
                        <?php endif; ?>
                        <!-- end: space tags -->
                        <br/>
                        <?php
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
                        ?>

                        <?php
                        // Follow Handling
                        if (!$space->isMember()) {
                            if ($space->isFollowedBy()) {
                                print CHtml::link(Yii::t('SpaceModule.views_space_indexPublic', "Unfollow"), $this->createUrl('//space/space/unfollow', array('sguid' => $space->guid)), array('class' => 'btn btn-danger'));
                            } else {
                                print CHtml::link(Yii::t('SpaceModule.views_space_indexPublic', "Follow"), $this->createUrl('//space/space/follow', array('sguid' => $space->guid)), array('class' => 'btn btn-success'));
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php $this->widget('application.modules_core.wall.widgets.WallStreamWidget', array('contentContainer' => $space)); ?>
    </div>
</div>



