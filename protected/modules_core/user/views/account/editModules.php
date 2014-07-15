<div class="panel-heading">
    <?php echo Yii::t('UserModule.views_account_editModules', '<strong>User</strong> modules'); ?>
</div>

<div class="panel-body">
    <p><?php echo Yii::t('UserModule.views_account_editModules', 'Enhance your profile with modules.'); ?></p><br>

    <ul class = "media-list">
        <?php foreach ($availableModules as $moduleId => $module): ?>
            <li class="media">
                <a class="pull-left" href="#">
                    <img src="<?php echo $module->getUserModuleImage(); ?>"
                         class="" width="64" height="64">
                </a>
                <div class="media-body">
                    <h4 class="media-heading"><?php echo $module->getUserModuleName(); ?></h4>
                    <p><?php echo $module->getUserModuleDescription(); ?></p>

                    <?php if ($user->isModuleEnabled($module->getId())) : ?>
                        <?php echo CHtml::link(Yii::t('UserModule.views_account_editModules', 'Disable'), array('//user/account/disableModule', 'moduleId' => $module->getId()), array('class' => 'btn btn-mini btn-danger', 'onClick' => 'return moduleDisableWarning()')); ?>

                        <?php if ($module->getUserModuleConfigUrl($user)) : ?>
                            <?php echo CHtml::link(Yii::t('UserModule.views_account_editModules', 'Configure'), $module->getUserModuleConfigUrl($user), array('class' => 'btn btn-mini')); ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php echo CHtml::link(Yii::t('UserModule.views_account_editModules', 'Enable'), array('//user/account/enableModule', 'moduleId' => $module->getId()), array('class' => 'btn btn-mini btn-success')); ?>
                    <?php endif; ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</div>