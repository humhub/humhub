<?php

use yii\helpers\Url;
use humhub\libs\Html;

/* @var $this View */
/* @var $modules array */
/* @var $categoryId int */
/* @var $keyword string */
/* @var $includeCommunityModules boolean */

?>
<div class="panel-body">

    <?= Html::beginForm(Url::to(['/marketplace/browse']), 'post', ['class' => 'form-search', 'id' => 'filterForm']); ?>
    <div class="row">
        <div class="col-md-6">
            <?= Html::dropDownList('categoryId', $categoryId, $categories, ['class' => 'form-control', 'data-ui-select2' => '', 'id' => 'categorySelect']); ?>
        </div>
        <div class="col-md-6">
            <div class="form-group form-group-search">
                <?= Html::textInput("keyword", $keyword, ["class" => "form-control form-search", "placeholder" => Yii::t('MarketplaceModule.base', 'search for available modules online')]); ?>
                <?= Html::submitButton(Yii::t('MarketplaceModule.base', 'Search'), ['class' => 'btn btn-default btn-sm form-button-search', 'data-ui-loader' => ""]); ?>
            </div>
        </div>
    </div>
    <?= Html::endForm(); ?>

    <?= Html::beginForm(Url::to(['/marketplace/browse', 'communitySwitch' => 1]), 'post', ['id' => 'communityForm']); ?>
    <div class="form-group pull-right">
        <label>
            <?= Html::checkbox('includeCommunityModules', $includeCommunityModules, ['id' => 'chkCommunity']); ?>
            <?= Yii::t('MarketplaceModule.base', 'Include Community Modules'); ?>
        </label>
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
            <?= $this->render('_module', ['module' => $module, 'licence' => $licence]); ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script <?= Html::nonce(); ?>>
    $('#categorySelect').change(function () {
        $('#filterForm').submit();
    });
    $('#chkCommunity').change(function () {
        $('#communityForm').submit();
    });
</script>


