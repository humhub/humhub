<div class="panel panel-default">
    <div class="panel-heading">
        <?php echo Yii::t('SpaceModule.views_admin_modules', '<strong>Space</strong> Modules'); ?>
    </div>
    <div class="panel-body">

        <?php if (count($availableModules) == 0): ?>
            <p><?php echo Yii::t('SpaceModule.views_admin_modules', 'Currently there are no modules available for this space!'); ?></p>
        <?php else: ?>
            <?php echo Yii::t('SpaceModule.views_admin_modules', 'Enhance this space with modules.'); ?><br>
        <?php endif; ?>


        <?php foreach ($availableModules as $moduleId => $module): ?>
            <hr>
            <div class="media">
                <img class="media-object img-rounded pull-left" data-src="holder.js/64x64" alt="64x64"
                     style="width: 64px; height: 64px;"
                     src="<?php echo $module->getSpaceModuleImage(); ?>">

                <div class="media-body">
                    <h4 class="media-heading"><?php echo $module->getSpaceModuleName(); ?>
                        <?php if ($this->getSpace()->isModuleEnabled($moduleId)) : ?>
                            <small><span class="label label-success"><?php echo Yii::t('SpaceModule.views_admin_modules', 'Activated'); ?></span></small>
                            <?php endif; ?>
                    </h4>

                    <p><?php echo $module->getSpaceModuleDescription(); ?></p>
                    <?php if ($this->getSpace()->isModuleEnabled($moduleId)) : ?>

                        <?php if ($this->getSpace()->canDisableModule($moduleId)): ?>
                            <?php echo HHtml::postLink(Yii::t('SpaceModule.views_admin_modules', 'Disable'), array('//space/admin/disableModule', 'moduleId' => $moduleId, 'sguid' => $this->getSpace()->guid), array('class' => 'btn btn-sm btn-primary', 'confirm' => Yii::t('SpaceModule.views_admin_modules', 'Are you sure? *ALL* module data for this space will be deleted!'))); ?>
                        <?php endif; ?>

                        <?php if ($module->getSpaceModuleConfigUrl($this->getSpace()) != "") : ?>
                            <?php
                            echo CHtml::link(
                                    Yii::t('SpaceModule.views_admin_modules', 'Configure'), $module->getSpaceModuleConfigUrl($this->getSpace()), array('class' => 'btn btn-default')
                            );
                            ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php echo HHtml::postLink(Yii::t('SpaceModule.views_admin_modules', 'Enable'), array('//space/admin/enableModule', 'moduleId' => $moduleId, 'sguid' => $this->getSpace()->guid), array('class' => 'btn btn-sm btn-primary')); ?>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Start: Module update message for the future -->
            <!--            <br>
                        <div class="alert alert-warning">
                            New Update for this module is available! <a href="#">See details</a>
                        </div>-->
            <!-- End: Module update message for the future -->

        <?php endforeach; ?>

    </div>
</div>