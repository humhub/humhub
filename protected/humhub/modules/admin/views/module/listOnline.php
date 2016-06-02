<?php

use yii\helpers\Url;
use yii\helpers\Html;
?>
<div class="panel panel-default">

    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_module_listOnline', '<strong>Modules</strong> directory'); ?></div>
    <?php echo $this->render('_header'); ?>

    <div class="panel-body">

        <!-- search form -->

        <?php echo Html::beginForm(Url::to(['//admin/module/list-online']), 'post', array('class' => 'form-search')); ?>
        <div class="row">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                <div class="form-group form-group-search">
                    <?php echo Html::textInput("keyword", $keyword, array("class" => "form-control form-search", "placeholder" => Yii::t('AdminModule.module_listOnline', 'search for available modules online'))); ?>
                    <?php echo Html::submitButton(Yii::t('AdminModule.module_listOnline', 'Search'), array('class' => 'btn btn-default btn-sm form-button-search', 'data-ui-loader' => "")); ?>
                </div>
            </div>
            <div class="col-md-3"></div>
        </div>
        <?php echo Html::endForm(); ?>

        <br/>

        <?php if (count($modules) == 0) : ?>

            <div class="text-center">
                <em><?php echo Yii::t('AdminModule.module_listOnline', 'No modules found!'); ?></em>
                <br/><br/>
            </div>

        <?php else: ?>


            <?php foreach ($modules as $module): ?>
                <hr/>
                <div class="media <?php if (Yii::$app->moduleManager->hasModule($module['id'])): ?>module-installed<?php endif; ?>">

                    <?php
                    $moduleImageUrl = Yii::getAlias('@web/img/default_module.jpg');
                    if (isset($module['moduleImageUrl']) && $module['moduleImageUrl'] != "") {
                        $moduleImageUrl = $module['moduleImageUrl'];
                    }
                    ?>
                    <?php if (isset($module['showDisclaimer']) && $module['showDisclaimer'] == 1): ?>
                        <small><a href="<?= Url::to(['thirdparty-disclaimer']); ?>" data-target="#globalModal">
                                <span class="label label-warning pull-right">
                                    <?php echo Yii::t('AdminModule.module_listOnline', 'Third-party'); ?>
                                </span>
                            </a></small>
                    <?php endif; ?>
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
                            <?php echo Yii::t('AdminModule.views_module_listOnline', 'Latest version:'); ?> <?php echo $module['latestVersion']; ?>
                            <?php if (isset($module['purchased']) && $module['purchased']) : ?>
                                &nbsp; Purchased
                            <?php endif; ?>

                            <?php if (isset($module['latestCompatibleVersion'])) : ?>

                                <?php if ($module['latestCompatibleVersion'] != $module['latestVersion']) : ?>
                                    &middot; <?php echo Yii::t('AdminModule.views_module_listOnline', 'Latest compatible version:'); ?>  <?php echo $module['latestCompatibleVersion']; ?>
                                <?php endif; ?>

                                <?php if (!Yii::$app->moduleManager->hasModule($module['id'])): ?>
                                    <?php if (isset($module['price_eur']) && $module['price_eur'] != 0 && !$module['purchased']) : ?>
                                        <?php $checkoutUrl = str_replace('-returnToUrl-', Url::to(['/admin/module/list-purchases'], true), $module['checkoutUrl']); ?>
                                        &middot; <?php echo Html::a(Yii::t('AdminModule.views_module_listOnline', 'Buy (%price%)', ['%price%' => $module['price_eur'] . '&euro;']), $checkoutUrl, array('style' => 'font-weight:bold', 'target' => '_blank')); ?>
                                    <?php else: ?>
                                        &middot; <?php echo Html::a(Yii::t('AdminModule.views_module_listOnline', 'Install'), Url::to(['install', 'moduleId' => $module['id']]), array('style' => 'font-weight:bold', 'data-loader' => "modal", 'data-message' => Yii::t('AdminModule.views_module_listOnline', 'Installing module...'), 'data-method' => 'POST')); ?>
                                    <?php endif; ?>
                                <?php endif; ?>

                            <?php else : ?>
                                &middot; <span
                                    style="color:red"><?php echo Yii::t('AdminModule.views_module_listOnline', 'No compatible module version found!'); ?></span>
                            <?php endif; ?>
                            &middot; <?php echo Html::a(Yii::t('AdminModule.views_module_listOnline', 'More info'), $module['marketplaceUrl'], array('target' => '_blank')); ?>
                            <?php if (isset($module['showDisclaimer']) && $module['showDisclaimer'] == 1): ?>
                                &middot; <?php echo Html::a(Yii::t('AdminModule.views_module_listOnline', 'Disclaimer'), Url::to(['thirdparty-disclaimer']), array('data-target' => '#globalModal')); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>