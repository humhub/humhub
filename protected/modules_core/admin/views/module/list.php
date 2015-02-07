<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_module_list', '<strong>Modules</strong> directory'); ?></div>
    <div class="panel-body">

        <?php echo $this->renderPartial('_header'); ?>

        <?php if (count($installedModules) == 0): ?>
            <br>
            <div><?php echo Yii::t('AdminModule.module_list', 'No modules installed yet. Install some to enhance the functionality!'); ?></div>
        <?php endif; ?>

        <?php foreach ($installedModules as $moduleId => $module) : ?>
            <hr/>
            <div class="media">
                <img class="media-object img-rounded pull-left" data-src="holder.js/64x64" alt="64x64"
                     style="width: 64px; height: 64px;"
                     src="<?php echo $module->getImage(); ?>">

                <div class="media-body">
                    <h4 class="media-heading"><?php echo $module->getName(); ?>
                        <small>
                            <?php if ($module->isEnabled()) : ?>
                                <span
                                    class="label label-success"><?php echo Yii::t('AdminModule.module_list', 'Activated'); ?></span>
                                <?php endif; ?>
                        </small>
                    </h4>


                    <p><?php echo $module->getDescription(); ?></p>

                    <div class="module-controls">

                        <?php echo Yii::t('AdminModule.module_list', 'Version:'); ?> <?php echo $module->getVersion(); ?>

                        <?php if ($module->isEnabled()) : ?>
                            <?php if ($module->getConfigUrl()) : ?>
                                &middot; <?php echo HHtml::link(Yii::t('AdminModule.views_module_list', 'Configure'), $module->getConfigUrl(), array('style' => 'font-weight:bold')); ?>
                            <?php endif; ?>

                            <?php if ($module->isSpaceModule() || $module->isUserModule()): ?>
                                &middot; <?php echo HHtml::link(Yii::t('AdminModule.views_module_list', 'Set as default'), array('//admin/module/setAsDefault', 'moduleId' => $moduleId), array('data-target' => '#globalModal', 'data-toggle' => 'modal')); ?>
                            <?php endif; ?>

                            &middot; <?php echo HHtml::postLink(Yii::t('AdminModule.views_module_list', 'Disable'), array('//admin/module/disable', 'moduleId' => $moduleId), array('confirm' => Yii::t('AdminModule.views_module_list', 'Are you sure? *ALL* module data will be lost!'))); ?>

                        <?php else: ?>
                            &middot; <?php echo HHtml::postLink(Yii::t('AdminModule.views_module_list', 'Enable'), array('//admin/module/enable', 'moduleId' => $moduleId), array('style' => 'font-weight:bold')); ?>
                        <?php endif; ?>

                        <?php if (Yii::app()->moduleManager->canUninstall($moduleId)): ?>
                            &middot; <?php echo HHtml::postLink(Yii::t('AdminModule.views_module_list', 'Uninstall'), array('//admin/module/uninstall', 'moduleId' => $moduleId), array('confirm' => Yii::t('AdminModule.views_module_list', 'Are you sure? *ALL* module related data and files will be lost!'))); ?>
                        <?php endif; ?>

                        &middot; <?php echo HHtml::link(Yii::t('AdminModule.views_module_list', 'More info'), array('//admin/module/info', 'moduleId' => $moduleId), array('data-target' => '#globalModal', 'data-toggle' => 'modal')); ?>

                    </div>

                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>