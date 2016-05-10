<?php

use yii\helpers\Url;
use yii\helpers\Html;
?>
<div class="panel panel-default">

    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_module_listOnline', '<strong>Modules</strong> directory'); ?></div>
    <?php echo $this->render('_header'); ?>
    
    <div class="panel-body"> 
        <!-- search form -->
        <?php echo Html::beginForm(Url::to(['//admin/module/list-purchases']), 'post', array('class' => 'form-search')); ?>
        <div class="row">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                <div class="form-group form-group-search">
                    <?php echo Html::textInput("licenceKey", $licenceKey, array("class" => "form-control form-search", "placeholder" => Yii::t('AdminModule.base', 'Add purchased module by licence key'))); ?>
                    <?php echo Html::submitButton(Yii::t('AdminModule.module_listOnline', 'Register'), array('class' => 'btn btn-default btn-sm form-button-search' , 'data-ui-loader' => "")); ?>
                </div>
                <?php if ($message != ""): ?>
                    <div style="color:<?php echo ($hasError) ? 'red' : 'green'; ?>"><?= Html::encode($message); ?></div>
                <?php endif; ?>
            </div>
            <div class="col-md-3"></div>
        </div>
        <?php echo Html::endForm(); ?>

        <br/>

        <?php if (count($modules) == 0) : ?>

            <div class="text-center">
                <em><?php echo Yii::t('AdminModule.module_listOnline', 'No purchased modules found!'); ?></em>
                <br/><br/>
            </div>

        <?php else: ?>


            <?php foreach ($modules as $module): ?>
                <hr/>
                <div class="media ">

                    <?php
                    $moduleImageUrl = Yii::getAlias('@web/img/default_module.jpg');
                    if (isset($module['moduleImageUrl']) && $module['moduleImageUrl'] != "") {
                        $moduleImageUrl = $module['moduleImageUrl'];
                    }
                    ?>

                    <img class="media-object img-rounded pull-left" data-src="holder.js/64x64" alt="64x64"
                         style="width: 64px; height: 64px;"
                         src="<?php echo $moduleImageUrl; ?>">

                    <div class="media-body">
                        <h4 class="media-heading"><?php echo $module['name']; ?>
                            <?php if (Yii::$app->moduleManager->hasModule($module['id'])): ?>
                                <small><span
                                        class="label label-success"><?php echo Yii::t('AdminModule.module_listOnline', 'Installed'); ?>
                                </small></span>
                            <?php endif; ?>
                        </h4>
                        <p><?php echo $module['description']; ?></p>

                        <div class="module-controls">
                            <?php if (!Yii::$app->moduleManager->hasModule($module['id'])): ?>
                                <?php echo Html::a(Yii::t('AdminModule.views_module_listOnline', 'Install'), Url::to(['install', 'moduleId' => $module['id']]), array('style' => 'font-weight:bold', 'data-loader' => "modal", 'data-message' => Yii::t('AdminModule.views_module_listOnline', 'Installing module...'), 'data-method' => 'POST')); ?>
                                &middot;
                            <?php endif; ?>
                            <?php echo Html::a(Yii::t('AdminModule.views_module_listOnline', 'More info'), $module['marketplaceUrl'], array('target' => '_blank')); ?>
                            &middot; <?php echo Yii::t('AdminModule.views_module_listOnline', 'Latest version:'); ?> <?php echo $module['latestVersion']; ?>
                            &middot; <?php echo Yii::t('AdminModule.views_module_listOnline', 'Licence Key:'); ?> <?php echo $module['licence_key']; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<small class="pull-right">Installation Id: <?php echo Yii::$app->getModule('admin')->settings->get('installationId'); ?></small>