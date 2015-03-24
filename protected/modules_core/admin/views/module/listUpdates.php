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
                            &middot; <?php echo HHtml::postLink(Yii::t('AdminModule.views_module_listUpdates', 'Update'), $this->createUrl('update', array('moduleId' => $module['id'])), array('style'=>'font-weight:bold', 'class' => 'process')); ?>
                            &middot; <?php echo HHtml::link(Yii::t('AdminModule.views_module_listOnline', 'More info'), $module['marketplaceUrl'], array('target' => '_blank')); ?>
                        <?php endif; ?>

                    </div>
                </div>
            </div>

        <?php endforeach; ?>

    </div>
</div>

<!-- start: Modal -->
<div class="modal" id="processModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><?php echo Yii::t('AdminModule.views_module_list', 'Processing...') ?></h4>
            </div>
            <div class="modal-body">
                <div class="loader" style="padding-top: 0;">
                    <div class="sk-spinner sk-spinner-three-bounce">
                        <div class="sk-bounce1"></div>
                        <div class="sk-bounce2"></div>
                        <div class="sk-bounce3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end: Modal -->

<script type="text/javascript">

    $('.process').click(function () {
        $('#processModal').modal('show');
    })

</script>