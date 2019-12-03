<?php

use yii\helpers\Url;
use yii\bootstrap\Html;

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
<script>
    $('#categorySelect').change(function () {
        $('#filterForm').submit();
    });
</script>


