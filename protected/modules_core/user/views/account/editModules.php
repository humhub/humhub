<div class="panel-heading">
    <?php echo Yii::t('UserModule.account', '<strong>User</strong> modules'); ?>
</div>

<div class="panel-body">
    <p><?php echo Yii::t('UserModule.account', 'Enhance your profile with modules.'); ?></p><br>

    <ul class = "media-list">
        <?php foreach ($user->getAvailableModules() as $moduleId => $moduleInfo): ?>
            <li class="media">
                <a class="pull-left" href="#">
                    <img src="<?php echo Yii::app()->createUrl('uploads/profile_image/default_module.jpg'); ?>"
                         class="" width="64" height="64">
                </a>
                <div class="media-body">
                    <h4 class="media-heading"><?php echo $moduleInfo['title']; ?></h4>
                    <p><?php echo $moduleInfo['description']; ?></p>

                    <?php if ($user->isModuleEnabled($moduleId)) : ?>
                        <?php echo CHtml::link(Yii::t('base', 'Disable'), array('//user/account/disableModule', 'moduleId' => $moduleId), array('class' => 'btn btn-mini btn-danger', 'onClick' => 'return moduleDisableWarning()')); ?>

                        <?php if (isset($moduleInfo['configRoute'])) : ?>
                            <?php echo CHtml::link(Yii::t('AdminModule.base', 'Configure'), array($moduleInfo['configRoute']), array('class' => 'btn btn-mini')); ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php echo CHtml::link(Yii::t('base', 'Enable'), array('//user/account/enableModule', 'moduleId' => $moduleId), array('class' => 'btn btn-mini btn-success')); ?>
                    <?php endif; ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</div>