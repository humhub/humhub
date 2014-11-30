<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_module_listUpdates', '<strong>Modules</strong> directory'); ?></div>
    <div class="panel-body">

        <?php echo $this->renderPartial('_header'); ?>

        <?php if (count($modules) == 0): ?>

            <div><?php echo Yii::t('AdminModule.module_listUpdates', 'All modules are up to date!'); ?></div>

        <?php endif; ?>

        <?php foreach ($modules as $module): ?>
            <hr/>

            <?php
            $moduleImageUrl = Yii::app()->baseUrl . '/img/default_module.jpg';
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
                            <?php echo Yii::t('AdminModule.views_module_listUpdates', 'Installed version:'); ?> <?php echo Yii::app()->moduleManager->getModule($module['id'])->getVersion(); ?>
                            &middot; <?php echo Yii::t('AdminModule.views_module_listUpdates', 'Latest compatible Version:'); ?> <?php echo $module['latestCompatibleVersion']; ?>
                            &middot; <?php echo HHtml::postLink(Yii::t('AdminModule.views_module_listUpdates', 'Update'), $this->createUrl('update', array('moduleId' => $module['id'])), array('style'=>'font-weight:bold')); ?>
                            &middot; <?php echo HHtml::link(Yii::t('AdminModule.views_module_listOnline', 'More info'), $module['marketplaceUrl'], array('target' => '_blank')); ?>
                        <?php endif; ?>

                    </div>
                </div>
            </div>

        <?php endforeach; ?>

    </div>
</div>