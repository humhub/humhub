<?php

use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="panel-heading">
    <?php echo Yii::t('UserModule.views_account_editModules', '<strong>User</strong> modules'); ?>
</div>

<div class="panel-body">
    <div class="help-block"><?php echo Yii::t('UserModule.views_account_editModules', 'Enhance your profile with modules.'); ?></div>

    <?php foreach ($availableModules as $moduleId => $module): ?>
        <hr>
        <div class="media">
            <a class="pull-left" href="#">
                <img src="<?php echo $module->getContentContainerImage($user); ?>"
                     class="" width="64" height="64">
            </a>
            <div class="media-body">
                <h4 class="media-heading"><?php echo $module->getContentContainerName($user); ?></h4>
                <p><?php echo $module->getContentContainerDescription($user); ?></p>

                <?php if ($user->isModuleEnabled($module->id)) : ?>
                    <?php if ($user->canDisableModule($moduleId)): ?>
                        <?php echo Html::a(Yii::t('UserModule.views_account_editModules', 'Disable'), Url::to(['/user/account/disable-module', 'moduleId' => $module->id]), array('class' => 'btn btn-sm btn-danger', 'data-method' => 'POST', 'data-confirm' => Yii::t('UserModule.views_account_editModules', 'Are you really sure? *ALL* module data for your profile will be deleted!'), 'data-ui-loader' => '')); ?>
                    <?php endif; ?>
                    <?php if ($module->getContentContainerConfigUrl($user)) : ?>
                        <?php echo Html::a(Yii::t('UserModule.views_account_editModules', 'Configure'), $module->getContentContainerConfigUrl($user), array('class' => 'btn btn-sm')); ?>
                    <?php endif; ?>
                <?php else: ?>
                    <?php echo Html::a(Yii::t('UserModule.views_account_editModules', 'Enable'), Url::to(['/user/account/enable-module', 'moduleId' => $module->id]), array('data-method' => 'POST', 'class' => 'btn btn-sm btn-primary', 'data-ui-loader' => '')); ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>