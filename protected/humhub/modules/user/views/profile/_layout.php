<?php
$user = $this->context->getUser();
?>
<div class="container profile-layout-container">
    <div class="row">
        <div class="col-md-12">
            <?= \humhub\modules\user\widgets\ProfileHeader::widget(['user' => $user]); ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-2 layout-nav-container">
            <?= \humhub\modules\user\widgets\ProfileMenu::widget(['user' => $this->context->user]); ?>
        </div>

        <?php if (isset($this->hideSidebar) && $this->hideSidebar) : ?>
            <div class="col-md-10 layout-content-container">
                <?php echo $content; ?>
            </div>
        <?php else: ?>
            <div class="col-md-7 layout-content-container">
                <?php echo $content; ?>
            </div>
            <div class="col-md-3 layout-sidebar-container">
                <?php
                echo \humhub\modules\user\widgets\ProfileSidebar::widget([
                    'widgets' => [
                        [\humhub\modules\user\widgets\UserTags::className(), ['user' => $this->context->user], ['sortOrder' => 10]],
                        [\humhub\modules\user\widgets\UserSpaces::className(), ['user' => $this->context->user], ['sortOrder' => 20]],
                        [\humhub\modules\user\widgets\UserFollower::className(), ['user' => $this->context->user], ['sortOrder' => 30]],
                    ]
                ]);

                /*
                  $this->widget('application.modules_core.user.widgets.ProfileSidebarWidget', array(
                  'widgets' => array(
                  array('application.modules_core.user.widgets.UserTagsWidget', array('user' => $this->context->getUser()), array('sortOrder' => 10)),
                  array('application.modules_core.user.widgets.UserSpacesWidget', array('user' => $this->context->getUser())),
                  array('application.modules_core.user.widgets.UserFollowerWidget', array('user' => $this->context->getUser())),
                  )
                  ));
                 *
                 */
                ?>
            </div>
        <?php endif; ?>
    </div>
</div>
