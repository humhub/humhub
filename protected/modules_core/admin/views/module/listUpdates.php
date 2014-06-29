<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.modules', '<strong>Modules</strong> directory'); ?></div>
    <div class="panel-body">

        <?php echo $this->renderPartial('_header'); ?>
        <br/>

        <h1><?php echo Yii::t('AdminModules.modules', '<strong>Available</strong> Updates'); ?></h1>

        <?php if (count($modules) == 0): ?>

            <div><?php echo Yii::t('AdminModules.modules', 'All modules are up to date!'); ?></div>

        <?php endif; ?>

        <?php foreach ($modules as $module): ?>

            <?php
            $moduleImageUrl = Yii::app()->baseUrl . '/uploads/profile_image/default_module.jpg';
            if (isset($module['moduleImageUrl']) && $module['moduleImageUrl'] != "") {
                $moduleImageUrl = $module['moduleImageUrl'];
            }
            ?>

            <div class="media">
                <img class="media-object img-rounded pull-left" data-src="holder.js/64x64" alt="64x64"
                     style="width: 64px; height: 64px;"
                     src="<?php echo $moduleImageUrl; ?>">

                <div class="media-body">
                    <h4 class="media-heading"><?php echo $module['name']; ?> </h4>

                    <p><?php echo $module['description']; ?></p>

                    <div class="module-controls">

                        <?php if (isset($module['latestCompatibleVersion']) && Yii::app()->moduleManager->isInstalled($module['id'])) : ?>
                            <?php echo Yii::t('AdminModule.modules', 'Installed version:'); ?> <?php echo Yii::app()->moduleManager->getModule($module['id'])->getVersion(); ?>
                            &middot; <?php echo Yii::t('AdminModule.modules', 'Latest compatible Version:'); ?> <?php echo $module['latestCompatibleVersion']; ?>
                            &middot; <?php echo HHtml::postLink(Yii::t('AdminModule.modules', 'Update'), $this->createUrl('update', array('moduleId' => $module['id']))); ?>
                            &middot; <?php echo HHtml::link(Yii::t('AdminModule.modules', 'More info'), array('//admin/module/infoOnline', 'moduleId' => $module['id']), array('data-target' => '#globalModal', 'data-toggle' => 'modal')); ?>
                        <?php endif; ?>

                    </div>
                </div>
            </div>

        <?php endforeach; ?>

    </div>
</div>