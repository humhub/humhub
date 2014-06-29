<div class="panel panel-default">
    <div class="panel-body">

        <?php echo $this->renderPartial('_header'); ?>

        <h2><?php echo Yii::t('AdminModules.modules', 'Available Updates'); ?></h2>

        <?php if (count($modules) == 0): ?>

            <div><?php echo Yii::t('AdminModules.modules', 'All modules are up to date!'); ?></div>

        <?php endif; ?>

        <?php foreach ($modules as $module): ?>

            <div class="media">
                <img class="media-object img-rounded pull-left" data-src="holder.js/64x64" alt="64x64"
                     style="width: 64px; height: 64px;"
                     src="<?php echo Yii::app()->baseUrl; ?>/uploads/profile_image/default_module.jpg">

                <div class="media-body">
                    <h4 class="media-heading"><?php echo $module['name']; ?> </h4>
                    <p><?php echo $module['description']; ?></p>
                    <p><small>
                            <?php if (isset($module['latestCompatibleVersion']) && Yii::app()->moduleManager->isInstalled($module['id'])) : ?>
                                <?php echo Yii::t('AdminModule.modules', 'Installed version:'); ?> <?php echo Yii::app()->moduleManager->getModule($module['id'])->getVersion(); ?> 
                                &middot; <?php echo Yii::t('AdminModule.modules', 'Latest compatible Version:'); ?> <?php echo $module['latestCompatibleVersion']; ?> 
                                &middot; <?php echo HHtml::postLink(Yii::t('AdminModule.modules', 'Update'), $this->createUrl('update', array('moduleId' => $module['id']))); ?>
                            <?php endif; ?>
                        </small>
                    </p>
                </div>
            </div>

        <?php endforeach; ?>

    </div>
</div>