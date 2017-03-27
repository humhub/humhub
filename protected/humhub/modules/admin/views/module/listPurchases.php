<?php

use yii\helpers\Url;
use yii\helpers\Html;
?>
<div class="panel panel-default">

    <div class="panel-heading"><?= Yii::t('AdminModule.views_module_listOnline', '<strong>Modules</strong> directory'); ?></div>
    <?= $this->render('_header'); ?>

    <div class="panel-body">
        <!-- search form -->
        <?= Html::beginForm(Url::to(['//admin/module/list-purchases']), 'post', ['class' => 'form-search']); ?>
        <div class="row">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                <div class="form-group form-group-search">
                    <?= Html::textInput("licenceKey", $licenceKey, ["class" => "form-control form-search", "placeholder" => Yii::t('AdminModule.base', 'Add purchased module by licence key')]); ?>
                    <?= Html::submitButton(Yii::t('AdminModule.module_listOnline', 'Register'), ['class' => 'btn btn-default btn-sm form-button-search' , 'data-ui-loader' => ""]); ?>
                </div>
                <?php if ($message != ""): ?>
                    <div style="color:<?= ($hasError) ? 'red' : 'green'; ?>"><?= Html::encode($message); ?></div>
                <?php endif; ?>
            </div>
            <div class="col-md-3"></div>
        </div>
        <?= Html::endForm(); ?>

        <br/>

        <?php if (count($modules) == 0) : ?>

            <div class="text-center">
                <em><?= Yii::t('AdminModule.module_listOnline', 'No purchased modules found!'); ?></em>
                <br><br>
            </div>

        <?php else: ?>


            <?php foreach ($modules as $module): ?>
                <hr>
                <div class="media ">

                    <?php
                    $moduleImageUrl = Yii::getAlias('@web-static/img/default_module.jpg');
                    if (isset($module['moduleImageUrl']) && $module['moduleImageUrl'] != "") {
                        $moduleImageUrl = $module['moduleImageUrl'];
                    }
                    ?>

                    <img class="media-object img-rounded pull-left" data-src="holder.js/64x64" alt="64x64" style="width: 64px; height: 64px;" src="<?= $moduleImageUrl; ?>">

                    <div class="media-body">
                        <h4 class="media-heading"><?= $module['name']; ?>
                            <?php if (Yii::$app->moduleManager->hasModule($module['id'])): ?>
                                <small><span
                                        class="label label-success"><?= Yii::t('AdminModule.module_listOnline', 'Installed'); ?>
                                </small></span>
                            <?php endif; ?>
                        </h4>
                        <p><?= $module['description']; ?></p>

                        <div class="module-controls">
                            <?php if (!Yii::$app->moduleManager->hasModule($module['id'])): ?>
                                <?= Html::a(Yii::t('AdminModule.views_module_listOnline', 'Install'), Url::to(['install', 'moduleId' => $module['id']]), ['style' => 'font-weight:bold', 'data-loader' => "modal", 'data-message' => Yii::t('AdminModule.views_module_listOnline', 'Installing module...'), 'data-method' => 'POST']); ?>
                                &middot;
                            <?php endif; ?>
                            <?= Html::a(Yii::t('AdminModule.views_module_listOnline', 'More info'), $module['marketplaceUrl'], ['target' => '_blank']); ?>
                            &middot; <?= Yii::t('AdminModule.views_module_listOnline', 'Latest version:'); ?> <?= $module['latestVersion']; ?>
                            &middot; <?= Yii::t('AdminModule.views_module_listOnline', 'Licence Key:'); ?> <?= $module['licence_key']; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<small class="pull-right">Installation Id: <?= Yii::$app->getModule('admin')->settings->get('installationId'); ?></small>