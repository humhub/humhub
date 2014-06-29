<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.modules', '<strong>Modules</strong> directory'); ?></div>
    <div class="panel-body">

        <?php echo $this->renderPartial('_header'); ?>
        <br/>

        <h1><?php echo Yii::t('AdminModule.modules', '<strong>Online</strong> available modules'); ?></h1>

        <?php foreach ($modules as $module): ?>

            <div class="media">
                
                <?php 
                $moduleImageUrl = Yii::app()->baseUrl. '/uploads/profile_image/default_module.jpg';
                if (isset($module['moduleImageUrl']) && $module['moduleImageUrl'] != "") {
                    $moduleImageUrl = $module['moduleImageUrl']; 
                } 
                ?>
                    
                <img class="media-object img-rounded pull-left" data-src="holder.js/64x64" alt="64x64"
                     style="width: 64px; height: 64px;"
                     src="<?php echo $moduleImageUrl; ?>">

                <div class="media-body">
                    <h4 class="media-heading"><?php echo $module['name']; ?>
                        <?php if (Yii::app()->moduleManager->isInstalled($module['id'])): ?>
                            <small><span
                                    class="label label-success"><?php echo Yii::t('AdminModules.modules', 'Installed'); ?>
                            </small></span>
                        <?php endif; ?>
                    </h4>
                    <p><?php echo $module['description']; ?></p>

                    <div class="module-controls">
                        <?php echo Yii::t('AdminModule.modules', 'Latest version:'); ?> <?php echo $module['latestVersion']; ?>

                        <?php if (isset($module['latestCompatibleVersion'])) : ?>

                            <?php if ($module['latestCompatibleVersion'] != $module['latestVersion']) : ?>
                                &middot; <?php echo Yii::t('AdminModule.modules', 'Latest compatible version:'); ?>  <?php echo $module['latestCompatibleVersion']; ?>
                            <?php endif; ?>

                            <?php if (!Yii::app()->moduleManager->isInstalled($module['id'])): ?>
                                &middot; <?php echo HHtml::postLink(Yii::t('AdminModule.modules', 'Install'), $this->createUrl('install', array('moduleId' => $module['id']))); ?>
                            <?php endif; ?>

                        <?php else : ?>
                            &middot; <span
                                style="color:red"><?php echo Yii::t('AdminModule.modules', 'No compatible module version found!'); ?></span>
                        <?php endif; ?>

                        &middot; <?php echo HHtml::link(Yii::t('AdminModule.modules', 'More info'), array('//admin/module/infoOnline', 'moduleId' => $module['id']), array('data-target' => '#globalModal', 'data-toggle' => 'modal')); ?>

                    </div>
                </div>
            </div>

        <?php endforeach; ?>
    </div>
</div>