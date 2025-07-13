<?php

use humhub\helpers\Html;
use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\SpaceNameColorInput;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;

/* @var $model Space */
/* @var $visibilityOptions array */
/* @var $joinPolicyOptions array */

$animation = $model->hasErrors() ? 'shake' : 'fadeIn';
?>

<?php $form = Modal::beginFormDialog([
    'title' => Yii::t('SpaceModule.manage', '<strong>Create</strong> Space'),
    'footer' => ModalButton::save(Yii::t('SpaceModule.manage', 'Next'))->submit(['/space/create/create']),
    'form' => ['enableClientValidation' => false],
]) ?>

    <?= SpaceNameColorInput::widget(['form' => $form, 'model' => $model, 'focus' => true]) ?>
    <?= $form->field($model, 'description') ?>

    <a data-bs-toggle="collapse" id="access-settings-link" href="#collapse-access-settings" style="font-size: 11px;">
        <i class="fa fa-caret-right"></i> <?= Yii::t('SpaceModule.manage', 'Advanced access settings') ?>
    </a>

    <div id="collapse-access-settings" class="panel-collapse collapse">
        <br>
        <div class="row">
            <div class="col-lg-6">
                <?= $form->field($model, 'visibility')->radioList($visibilityOptions)->hint(false) ?>
            </div>
            <div class="col-lg-6 spaceJoinPolicy">
                <?= $form->field($model, 'join_policy')->radioList($joinPolicyOptions)->hint(false) ?>
            </div>
        </div>
    </div>

<?php Modal::endFormDialog(); ?>

<script <?= Html::nonce() ?>>

    var $checkedVisibility = $('input[type=radio][name="Space[visibility]"]:checked');
    if ($checkedVisibility.length && $checkedVisibility[0].value == 0) {
        $('.spaceJoinPolicy').hide();
    }

    $('input[type=radio][name="Space[visibility]"]').on('change', function () {
        if (this.value == 0) {
            $('.spaceJoinPolicy').fadeOut();
        } else {
            $('.spaceJoinPolicy').fadeIn();
        }
    });

    $('#collapse-access-settings').on('show.bs.collapse', function () {
        // change link arrow
        $('#access-settings-link i').removeClass('fa-caret-right');
        $('#access-settings-link i').addClass('fa-caret-down');
    });

    $('#collapse-access-settings').on('hide.bs.collapse', function () {
        // change link arrow
        $('#access-settings-link i').removeClass('fa-caret-down');
        $('#access-settings-link i').addClass('fa-caret-right');
    });

</script>
