<?php

use yii\helpers\Url;
use yii\helpers\Html;

?>
<div class="panel-body">

    <?= Html::beginForm(Url::to(['/marketplace/browse']), 'post', ['class' => 'form-search']); ?>
    <div class="row">
        <div class="col-md-3"></div>
        <div class="col-md-6">
            <div class="form-group form-group-search">
                <?= Html::textInput("keyword", $keyword, ["class" => "form-control form-search", "placeholder" => Yii::t('MarketplaceModule.base', 'search for available modules online')]); ?>
                <?= Html::submitButton(Yii::t('MarketplaceModule.base', 'Search'), ['class' => 'btn btn-default btn-sm form-button-search', 'data-ui-loader' => ""]); ?>
            </div>
        </div>
        <div class="col-md-3"></div>
    </div>
    <?= Html::endForm(); ?>

    <br>

    <?php if (count($modules) == 0) : ?>

        <div class="text-center">
            <em><?= Yii::t('MarketplaceModule.base', 'No modules found!'); ?></em>
            <br><br>
        </div>

    <?php else: ?>
        <?php foreach ($modules as $module): ?>
            <?= $this->render('_module', ['module' => $module]); ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
