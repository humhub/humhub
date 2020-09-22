<?php

use humhub\modules\marketplace\models\Licence;
use humhub\widgets\Button;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;


/* @var $this \humhub\components\View */
/* @var $model Licence */
?>

<div class="panel">

    <div class="panel-heading">
        <?= Yii::t('MarketplaceModule.base', '<strong>Activate</strong> your Professional Edition'); ?>
    </div>

    <div class="panel-body">

        <?php if ($model->type === Licence::LICENCE_TYPE_PRO): ?>
            <div class="alert alert-success">
                <p>
                    <strong>
                        <?= Yii::t('MarketplaceModule.base', 'Professional Edition is activated!'); ?>
                    </strong><br/>
                    <?= Yii::t('MarketplaceModule.base',
                        'Licenced for max. {number} users.', ['number' => $model->maxUsers]); ?>
                </p>
            </div>
        <?php endif; ?>

        <p>
            <?= Yii::t('MarketplaceModule.base',
                'No license key? Find out more about the {pro} or contact us.',
                ['pro' => Html::a('Professional Edition', 'https://www.humhub.com',
                    ['target' => '_blank', 'style' => 'text-decoration:underline'])]
            ); ?></p>
        <hr>

        <?php $form = ActiveForm::begin([
            'id' => 'licence-form',
            'enableAjaxValidation' => false,
            'enableClientValidation' => false]); ?>

        <?= $form->errorSummary($model); ?>
        <?= $form->field($model, 'licenceKey')->textInput(); ?>
        <hr>

        <?= Button::save(Yii::t('MarketplaceModule.base', 'Save and update'))->submit(); ?>

        <?php ActiveForm::end(); ?>

        <?php if ($model->type === Licence::LICENCE_TYPE_PRO): ?>
            <a href="<?= Url::to(['/marketplace/licence/remove']); ?>" class="pull-right">
                <small><?= Yii::t('MarketplaceModule.base', 'Remove licence key'); ?></small>
            </a>
        <?php endif; ?>

    </div>

</div>

