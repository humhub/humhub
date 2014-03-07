<div class="panel panel-default">
    <div class="panel-heading">
        <?php echo Yii::t('SpaceModule.base', 'Modules'); ?>
    </div>
    <div class="panel-body">
        <?php echo Yii::t('SpaceModule.base', 'Enhance this space with modules.'); ?><br>


        <?php foreach ($this->getSpace()->getAvailableModules() as $moduleId => $moduleInfo): ?>
            <div class="media">
                <img class="media-object img-rounded pull-left" data-src="holder.js/64x64" alt="64x64"
                     style="width: 64px; height: 64px;"
                     src="<?php echo Yii::app()->baseUrl; ?>/uploads/profile_image/default_module.jpg">

                <div class="media-body">
                    <h4 class="media-heading"><?php echo $moduleInfo['title']; ?>
                        <?php if ($this->getSpace()->isModuleEnabled($moduleId)) : ?>
                            <small><span class="label label-success"><?php echo Yii::t('SpaceModule.base', 'Activated'); ?></span></small>
                        <?php endif; ?>
                    </h4>

                    <p><?php echo $moduleInfo['description']; ?></p>
                    <?php if ($this->getSpace()->isModuleEnabled($moduleId)) : ?>
                        <?php echo CHtml::link(Yii::t('base', 'Disable'), array('//space/admin/disableModule', 'moduleId' => $moduleId, 'sguid' => $this->getSpace()->guid), array('class' => 'btn btn-sm btn-primary', 'onClick' => 'return moduleDisableWarning()')); ?>

                        <?php if (isset($moduleInfo['configRoute'])) : ?>
                            <?php
                            echo CHtml::link(
                                    Yii::t('SpaceModule.base', 'Configure'), $this->createUrl($moduleInfo['configRoute'], array('sguid' => $this->getSpace()->guid)), array('class' => 'btn btn-default')
                            );
                            ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php echo CHtml::link(Yii::t('base', 'Enable'), array('//space/admin/enableModule', 'moduleId' => $moduleId, 'sguid' => $this->getSpace()->guid), array('class' => 'btn btn-sm btn-primary')); ?>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Start: Module update message for the future -->
            <!--            <br>
                        <div class="alert alert-warning">
                            New Update for this module is available! <a href="#">See details</a>
                        </div>-->
            <!-- End: Module update message for the future -->
            <hr>
        <?php endforeach; ?>

    </div>
</div>