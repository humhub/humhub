<?php
use humhub\widgets\ActiveForm;
use humhub\widgets\ModalButton;
use humhub\widgets\ModalDialog;
use yii\helpers\Url;

$animation = $model->hasErrors() ? 'shake' : 'fadeIn';
?>

<?php ModalDialog::begin(['header' => Yii::t('SpaceModule.views_create_create', '<strong>Create</strong> new space'), 'size' => 'small']) ?>
    <?php $form = ActiveForm::begin(); ?>
        <div class="modal-body">

            <?= humhub\modules\space\widgets\SpaceNameColorInput::widget(['form' => $form, 'model' => $model]) ?>

            <?php echo $form->field($model, 'description')->textarea(['placeholder' => Yii::t('SpaceModule.views_create_create', 'space description'), 'rows' => '3']); ?>

            <a data-toggle="collapse" id="access-settings-link" href="#collapse-access-settings" style="font-size: 11px;">
                <i class="fa fa-caret-right"></i> <?php echo Yii::t('SpaceModule.views_create_create', 'Advanced access settings'); ?>
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
            <?= ModalButton::submitModal(Url::to(['/space/create/create']), Yii::t('SpaceModule.views_create_create', 'Next')) ?>
            <?php /** ModalButton::submitModal(Url::to(['/space/create/create', 'skip' => 1]), Yii::t('SpaceModule.views_create_create', 'Skip'))
                ->setType('default')->icon('fa-forward', true)->cssClass('tt')->options(['title' => Yii::t('SpaceModule.views_create_create', 'Skip other steps')]) */?>
        </div>
    <?php ActiveForm::end(); ?>
<?php ModalDialog::end(); ?>

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