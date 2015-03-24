<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_module_listOnline', '<strong>Modules</strong> directory'); ?></div>
    <div class="panel-body">

        <?php echo $this->renderPartial('_header'); ?>
        <br/><br/>

        <!-- search form -->

        <?php echo CHtml::form(Yii::app()->createUrl('//admin/module/listOnline', array()), 'post', array('class' => 'form-search')); ?>
        <div class="row">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                <div class="form-group form-group-search">
                    <?php echo CHtml::textField("keyword", $keyword, array("class" => "form-control form-search", "placeholder" => Yii::t('AdminModules.module_listOnline', 'search for available modules online'))); ?>
                    <?php echo CHtml::submitButton(Yii::t('AdminModule.module_listOnline', 'Search'), array('class' => 'btn btn-default btn-sm form-button-search')); ?>
                </div>
            </div>
            <div class="col-md-3"></div>
        </div>
        <?php echo CHtml::endForm(); ?>

        <br/>

        <?php if (count($modules) == 0) : ?>

            <div class="text-center">
                <em><?php echo Yii::t('AdminModule.module_listOnline', 'No modules found!'); ?></em>
                <br/><br/>
            </div>

        <?php else: ?>


        <?php foreach ($modules as $module): ?>
            <hr/>
            <div class="media <?php if (Yii::app()->moduleManager->isInstalled($module['id'])): ?>module-installed<?php endif; ?>">

                <?php
                $moduleImageUrl = Yii::app()->baseUrl. '/img/default_module.jpg';
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
                                    class="label label-success"><?php echo Yii::t('AdminModule.module_listOnline', 'Installed'); ?>
                            </small></span>
                        <?php endif; ?>
                    </h4>
                    <p><?php echo $module['description']; ?></p>

                    <div class="module-controls">
                        <?php echo Yii::t('AdminModule.views_module_listOnline', 'Latest version:'); ?> <?php echo $module['latestVersion']; ?>

                        <?php if (isset($module['latestCompatibleVersion'])) : ?>

                            <?php if ($module['latestCompatibleVersion'] != $module['latestVersion']) : ?>
                                &middot; <?php echo Yii::t('AdminModule.views_module_listOnline', 'Latest compatible version:'); ?>  <?php echo $module['latestCompatibleVersion']; ?>
                            <?php endif; ?>

                            <?php if (!Yii::app()->moduleManager->isInstalled($module['id'])): ?>
                                &middot; <?php echo HHtml::postLink(Yii::t('AdminModule.views_module_listOnline', 'Install'), $this->createUrl('install', array('moduleId' => $module['id'])), array('style'=>'font-weight:bold', 'class' => 'process')); ?>
                            <?php endif; ?>

                        <?php else : ?>
                            &middot; <span
                                style="color:red"><?php echo Yii::t('AdminModule.views_module_listOnline', 'No compatible module version found!'); ?></span>
                        <?php endif; ?>
                        &middot; <?php echo HHtml::link(Yii::t('AdminModule.views_module_listOnline', 'More info'), $module['marketplaceUrl'], array('target' => '_blank')); ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php endif;?>
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