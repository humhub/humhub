<?php
use humhub\widgets\ActiveForm;
use yii\helpers\Url;

$animation = $model->hasErrors() ? 'shake' : 'fadeIn';
?>

<div class="modal-dialog modal-dialog-small animated <?= $animation ?>">
    <div class="modal-content">
        <?php $form = ActiveForm::begin(); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"
                    id="myModalLabel"><?php echo Yii::t('SpaceModule.views_create_create', '<strong>Create</strong> new space'); ?></h4>
            </div>
            <div class="modal-body">

                 <?= humhub\modules\space\widgets\SpaceNameColorInput::widget(['form' => $form, 'model' => $model])?>

                <?php echo $form->field($model, 'description')->textarea(['placeholder' => Yii::t('SpaceModule.views_create_create', 'space description'), 'rows' => '3']); ?>

                <a data-toggle="collapse" id="access-settings-link" href="#collapse-access-settings"
                   style="font-size: 11px;"><i
                        class="fa fa-caret-right"></i> <?php echo Yii::t('SpaceModule.views_create_create', 'Advanced access settings'); ?>
                </a>

                <div id="collapse-access-settings" class="panel-collapse collapse">
                    <br/>
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'join_policy')->radioList($joinPolicyOptions); ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'visibility')->radioList($visibilityOptions); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <a href="#" class="btn btn-primary" 
                   data-action-click="ui.modal.submit" 
                   data-ui-loader
                   data-action-url="<?= Url::to(['/space/create/create']) ?>">
                    <?= Yii::t('SpaceModule.views_create_create', 'Next'); ?>
                </a>
            </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<script type="text/javascript">
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