<?php

use humhub\components\View;
use humhub\modules\marketplace\models\Licence;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\bootstrap\Link;
use humhub\widgets\form\ActiveForm;
use yii\helpers\Url;

/* @var $this View */
/* @var $model Licence */
?>

<div class="panel">
    <div class="panel-heading">
        <?= Yii::t('MarketplaceModule.base', '<strong>Activate</strong> your Professional Edition') ?>
    </div>

    <div class="panel-body">

        <?php if ($model->type === Licence::LICENCE_TYPE_PRO): ?>
            <div class="alert alert-success">
                <p>
                    <strong>
                        <?= Yii::t('MarketplaceModule.base', 'Professional Edition is activated!') ?>
                    </strong><br/>
                    <?= Yii::t(
                        'MarketplaceModule.base',
                        'Licensed for max. {number} users.',
                        ['number' => $model->maxUsers],
                    ) ?>
                </p>
            </div>
        <?php endif; ?>

        <p>
            <?= Yii::t(
                'MarketplaceModule.base',
                'No license key? Find out more about the {pro} or contact us.',
                ['pro' => Link::to('Professional Edition', 'https://www.humhub.com')->blank()->cssClass('link-accent')],
            ) ?></p>
        <hr>

        <?php $form = ActiveForm::begin([
            'id' => 'licence-form',
            'enableAjaxValidation' => false,
            'enableClientValidation' => false]); ?>

        <?= $form->errorSummary($model); ?>
        <?= $form->field($model, 'licenceKey')->textInput() ?>
        <hr>

        <?= Button::save(Yii::t('MarketplaceModule.base', 'Save and update'))->submit() ?>

        <?php ActiveForm::end(); ?>

        <?php if ($model->type === Licence::LICENCE_TYPE_PRO): ?>
            <a href="<?= Url::to(['/marketplace/licence/remove']); ?>" class="float-end">
                <small><?= Yii::t('MarketplaceModule.base', 'Remove license key'); ?></small>
            </a>
        <?php endif; ?>
    </div>
</div>
