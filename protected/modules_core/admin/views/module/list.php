<h1><?php echo Yii::t('AdminModule.base', 'Modules'); ?></h1>

<ul class="nav nav-pills" id="moduleTabs">
    <li class="active"><a href="#extensions">Installed</a></li>
    <!--<li><?php echo CHtml::link('Browse online', $this->createUrl('listOnline')); ?></li>-->
</ul>
<br>

<h2>Installed Modules</h2>


<?php foreach ($installedModules as $moduleId => $module) : ?>
    <div class="media">
        <img class="media-object img-rounded pull-left" data-src="holder.js/64x64" alt="64x64"
             style="width: 64px; height: 64px;"
             src="<?php echo $module->getImage(); ?>">

        <div class="media-body">
            <h4 class="media-heading"><?php echo $module->getName(); ?>
                <small>
                    <?php if ($module->isEnabled()) : ?>
                        <span
                            class="label label-success"><?php echo Yii::t('SpaceModule.base', 'Activated'); ?></span>
                    <?php endif; ?>
                </small>
            </h4>

            <p><?php echo $module->getDescription(); ?></p>

            <p><small>
                    <?php if ($module->isEnabled()) : ?>
                        <?php echo CHtml::link(Yii::t('AdminModule.modules', 'Disable'), array('//admin/module/disable', 'moduleId' => $moduleId)); ?>

                        <?php if ($module->getConfigUrl()) : ?>
                            &middot; <?php echo CHtml::link(Yii::t('AdminModule.modules', 'Configure'), $module->getConfigUrl()); ?>
                        <?php endif; ?>
                    <?php else: ?> 
                        <?php echo CHtml::link(Yii::t('AdminModule.modules', 'Enable'), array('//admin/module/enable', 'moduleId' => $moduleId)); ?>
                    <?php endif; ?>
                    
                    <!--
                    &middot; <?php echo CHtml::link(Yii::t('AdminModule.modules', 'Uninstall'), array('//admin/module/uninstall', 'moduleId' => $moduleId)); ?>
                    -->
                    
                    <!--
                    &middot; <a href="#"> Details</a>
                    &middot; <a href="#"> Uninstall</a>
                    -->
                </small></p>

        </div>
    </div>
<?php endforeach; ?>
