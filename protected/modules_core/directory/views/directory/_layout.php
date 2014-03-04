<?php
/**
 * Layout for directory module
 *
 * @package humhub.modules_core.directory.views
 * @since 0.5
 */
?>

<div class="container">

    <div class="row">
        <div class="col-md-2">
            <!-- show directory menu widget -->
            <div class="list-group">
                <span class="list-group-item"><h4><?php echo Yii::t('DirectoryModule.base', 'Directory'); ?></h4></span>

                <?php if (Group::model()->count() > 1) : ?>
                    <a href="<?php echo Yii::app()->createUrl('//directory/directory/groups'); ?>"
                       class="list-group-item <?php
                       if ($this->action->id == "groups") {
                           echo "active";
                       }
                       ?>">
                        <div>
                            <div class="edit_group"><?php echo Yii::t('DirectoryModule.base', 'Groups'); ?></div>
                        </div>
                    </a>
                <?php endif; ?>

                <a href="<?php echo Yii::app()->createUrl('//directory/directory/members'); ?>"
                   class="list-group-item <?php
                   if ($this->action->id == "members") {
                       echo "active";
                   }
                   ?>">
                    <div>
                        <div class="user_details"><?php echo Yii::t('DirectoryModule.base', 'Members'); ?></div>
                    </div>
                </a>

                <a href="<?php echo Yii::app()->createUrl('//directory/directory/spaces'); ?>"
                   class="list-group-item <?php
                   if ($this->action->id == "spaces") {
                       echo "active";
                   }
                   ?>">
                    <div>
                        <div class="workspaces"><?php echo Yii::t('DirectoryModule.base', 'Spaces'); ?></div>
                    </div>
                </a>


                <a href="<?php echo Yii::app()->createUrl('//directory/directory/userPosts'); ?>"
                   class="list-group-item <?php
                   if ($this->action->id == "userPosts") {
                       echo "active";
                   }
                   ?>">
                    <div>
                        <div class="stream"><?php echo Yii::t('DirectoryModule.base', 'User profile posts'); ?></div>
                    </div>
                </a>
            </div>
        </div>
        <div class="col-md-7">
            <!-- show content -->
            <?php echo $content; ?>
        </div>
        <div class="col-md-3">
            <!-- show directory sidebar stream -->
            <?php
            $this->widget('application.modules_core.directory.widgets.DirectorySidebarWidget', array(
                'widgets' => array()
            ));
            ?>
        </div>
    </div>

</div>
