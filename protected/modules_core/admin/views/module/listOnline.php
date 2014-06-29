<div class="panel panel-default">
    <div class="panel-body">

        <?php echo $this->renderPartial('_header'); ?>

        <h2><?php echo Yii::t('AdminModule.modules', 'Online available modules'); ?></h2>

        <?php foreach ($modules as $module): ?>

            <div class="media">
                <img class="media-object img-rounded pull-left" data-src="holder.js/64x64" alt="64x64"
                     style="width: 64px; height: 64px;"
                     src="<?php echo Yii::app()->baseUrl; ?>/uploads/profile_image/default_module.jpg">

                <div class="media-body">
                    <h4 class="media-heading"><?php echo $module['name']; ?> 
                        <?php if (Yii::app()->moduleManager->isInstalled($module['id'])): ?>
                            <small><span class="label label-success"><?php echo Yii::t('AdminModules.modules', 'Installed'); ?></small></span>
                        <?php endif; ?>
                    </h4>
                    <p><?php echo $module['description']; ?></p>
                    <p><small>

                            <?php echo Yii::t('AdminModule.modules', 'Latest version:'); ?> <?php echo $module['latestVersion']; ?> 

                            <?php if (isset($module['latestCompatibleVersion'])) : ?>

                                <?php if ($module['latestCompatibleVersion'] != $module['latestVersion']) : ?>
                                    &middot; <?php echo Yii::t('AdminModule.modules', 'Latest compatible version:'); ?>  <?php echo $module['latestCompatibleVersion']; ?> 
                                <?php endif; ?>

                                <?php if (!Yii::app()->moduleManager->isInstalled($module['id'])): ?>
                                    &middot; <?php echo HHtml::postLink(Yii::t('AdminModule.modules', 'Install'), $this->createUrl('install', array('moduleId' => $module['id']))); ?>
                                <?php endif; ?>

                            <?php else : ?>
                                &middot; <span style="color:red"><?php echo Yii::t('AdminModule.modules', 'No compatible module version found!'); ?></span>
                            <?php endif; ?>
                                
                            &middot; <?php echo HHtml::link(Yii::t('AdminModule.modules', 'More info'), array('//admin/module/infoOnline', 'moduleId' => $module['id']), array('data-target'=>'#globalModal', 'data-toggle'=>'modal')); ?>
                                
                        </small>
                    </p>
                </div>
            </div>

        <?php endforeach; ?>
    </div>
</div>