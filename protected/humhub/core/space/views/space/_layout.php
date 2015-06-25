<?php
$space = $this->context->contentContainer;
?>
<div class="container space-layout-container">
    <div class="row">
        <div class="col-md-12">
            <?php echo humhub\core\space\widgets\SpaceHeaderWidget::widget(['space' => $space]); ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-2 layout-nav-container">
            <?php echo \humhub\core\space\widgets\SpaceMenuWidget::widget(['space' => $space]); ?>
            <?php echo \humhub\core\space\widgets\SpaceAdminMenuWidget::widget(['space' => $space]); ?>
            <br/>
        </div>

        <?php if (isset($this->context->hideSidebar) && $this->context->hideSidebar) : ?>
            <div class="col-md-10 layout-content-container">
                <?php echo $content; ?>
            </div>
        <?php else: ?>
            <div class="col-md-7 layout-content-container">
                <?php echo $content; ?>
            </div>
            <div class="col-md-3 layout-sidebar-container">
                <?php
                /*
                  $this->widget('application.modules_core.space.widgets.SpaceSidebarWidget', array(
                  'widgets' => array(
                  array('application.modules_core.activity.widgets.ActivityStreamWidget', array('contentContainer' => $this->getSpace(), 'streamAction' => '//space/space/stream'), array('sortOrder' => 100)),
                  array('application.modules_core.space.widgets.SpaceMemberWidget', array('space' => $this->getSpace()), array('sortOrder' => 200)),
                  )
                  ));
                 * 
                 */
                ?>
            </div>
        <?php endif; ?>
    </div>
</div>
