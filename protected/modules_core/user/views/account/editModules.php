<div class="panel-heading">
    <?php echo Yii::t('UserModule.views_account_editModules', '<strong>User</strong> modules'); ?>
</div>

<div class="panel-body">
    <p><?php echo Yii::t('UserModule.views_account_editModules', 'Enhance your profile with modules.'); ?></p>

        <?php foreach ($availableModules as $moduleId => $module): ?>
            <hr>
            <div class="media">
                <a class="pull-left" href="#">
                    <img src="<?php echo $module->getUserModuleImage(); ?>"
                         class="" width="64" height="64">
                </a>
                <div class="media-body">
                    <h4 class="media-heading"><?php echo $module->getUserModuleName(); ?></h4>
                    <p><?php echo $module->getUserModuleDescription(); ?></p>

                    <?php if ($user->isModuleEnabled($module->getId())) : ?>
                        <?php if ($user->canDisableModule($moduleId)): ?>
                            <?php echo HHtml::postLink(Yii::t('UserModule.views_account_editModules', 'Disable'), array('//user/account/disableModule', 'moduleId' => $module->getId()), array('class' => 'btn btn-sm btn-danger', 'confirm' => Yii::t('UserModule.views_account_editModules', 'Are you really sure? *ALL* module data for your profile will be deleted!'))); ?>
                        <?php endif; ?>
                        <?php if ($module->getUserModuleConfigUrl($user)) : ?>
                            <?php echo CHtml::link(Yii::t('UserModule.views_account_editModules', 'Configure'), $module->getUserModuleConfigUrl($user), array('class' => 'btn btn-sm')); ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php echo HHtml::postLink(Yii::t('UserModule.views_account_editModules', 'Enable'), array('//user/account/enableModule', 'moduleId' => $module->getId()), array('class' => 'btn btn-sm btn-primary')); ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
</div>