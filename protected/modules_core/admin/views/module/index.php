<h1><?php echo Yii::t('AdminModule.base', 'Modules'); ?></h1>

<p>Manage installed/active modules in your HumHub Installation!</p><br>


<ul class="nav nav-pills" id="moduleTabs">
    <li class="active"><a href="#extensions">Third party modules</a></li>
    <li><a href="#core">Installed core modules</a></li>
</ul>
<br>
<hr>

<div class="tab-content">
    <div class="tab-pane active" id="extensions">

        <?php foreach (Yii::app()->moduleManager->getRegisteredModules() as $moduleDefinition) : ?>

            <?php
            $moduleId = $moduleDefinition['id'];
            ?>

            <?php if (!$moduleDefinition['isCoreModule']) : ?>

                <div class="media">
                    <img class="media-object img-rounded pull-left" data-src="holder.js/64x64" alt="64x64"
                         style="width: 64px; height: 64px;"
                         src="<?php echo Yii::app()->baseUrl; ?>/uploads/profile_image/default_module.jpg">

                    <div class="media-body">
                        <h4 class="media-heading"><?php echo $moduleDefinition['title']; ?>
                            <small>
                                <?php if (Yii::app()->moduleManager->isEnabled($moduleId)) : ?>
                                    <span
                                        class="label label-success"><?php echo Yii::t('SpaceModule.base', 'Activated'); ?></span>
                                <?php endif; ?>
                            </small>
                        </h4>

                        <p><?php echo $moduleDefinition['description']; ?></p>
                        <?php if (Yii::app()->moduleManager->isEnabled($moduleId)) : ?>
                            <?php echo CHtml::link(Yii::t('base', 'Disable'), array('//admin/module/disable', 'moduleId' => $moduleId), array('class' => 'btn btn-sm btn-primary', 'onClick' => 'return moduleDisableWarning()')); ?>

                            <?php if (isset($moduleDefinition['configRoute']) && $moduleDefinition['configRoute'] != "") : ?>
                                <?php echo CHtml::link(Yii::t('AdminModule.base', 'Configure'), array($moduleDefinition['configRoute']), array('class' => 'btn btn-default btn-sm')); ?>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php echo CHtml::link(Yii::t('base', 'Enable'), array('//admin/module/enable', 'moduleId' => $moduleId), array('class' => 'btn btn-sm btn-primary')); ?>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- Start: Module update message for the future -->
                <!--            <br>
                            <div class="alert alert-warning">
                                New Update for this module is available! <a href="#">See details</a>
                            </div>-->
                <!-- End: Module update message for the future -->
                <hr>
            <?php endif; ?>
        <?php endforeach; ?>


    </div>

    <div class="tab-pane" id="core">


        <?php foreach (Yii::app()->moduleManager->getRegisteredModules() as $moduleDefinition) : ?>

            <?php if ($moduleDefinition['isCoreModule']) : ?>

                <div class="media">
                    <img class="media-object img-rounded pull-left" data-src="holder.js/64x64" alt="64x64"
                         style="width: 64px; height: 64px;"
                         src="<?php echo Yii::app()->baseUrl; ?>/uploads/profile_image/default_module.jpg">

                    <div class="media-body">
                        <h4 class="media-heading"><?php echo $moduleDefinition['title']; ?></h4>
                        <p><?php echo $moduleDefinition['description']; ?></p>
                    </div>
                </div>
                <hr>
            <?php endif; ?>
        <?php endforeach; ?>


    </div>
</div>

<script>

    $('#moduleTabs a').click(function (e) {
        e.preventDefault();
        $(this).tab('show');
    })


    function moduleDisableWarning() {
        return confirm("<?php echo Yii::t('AdminModule.base', 'Are you really sure?\nAll module specific content will be ***DELETED***!'); ?>");
    }
</script>

