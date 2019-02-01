<div class="modal-dialog modal-dialog-normal animated fadeIn">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="myModalLabel">
                <?= Yii::t('AdminModule.views_module_restrictInstallation', 'Restrict installation for module - %moduleName%', ['%moduleName%' => "<strong>" . $module->getName() . "</strong>"]); ?>
            </h4>
        </div>
        <?php $form = humhub\compat\CActiveForm::begin(); ?>
        <div class="modal-body">

            <p>
                <?= Yii::t('AdminModule.views_module_restrictInstallation', 'Here you can set up module restriction installation settings.'); ?>
            </p>

            <br>

            <div class="row">
                <div class="col-md-6">
                    <?php if (! $adminOnly) : ?>
                        <div class="radio">
                            <label>
                                <?= $form->checkBox($model, 'onlyForSpaces'); ?>
                                <?= Yii::t('AdminModule.views_module_restrictInstallation', 'Only for spaces'); ?>
                            </label>
                        </div>
                        <div class="radio">
                            <label>
                                <?= $form->checkBox($model, 'onlyForProfiles'); ?>
                                <?= Yii::t('AdminModule.views_module_restrictInstallation', 'Only for profiles'); ?>
                            </label>
                        </div>
                    <?php endif; ?>
                    <div class="radio">
                        <label>
                            <?= $form->checkBox($model, 'onlyForAdmins'); ?>
                            <?= Yii::t('AdminModule.views_module_restrictInstallation', 'Only for admins'); ?>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">

            <?=
            \humhub\widgets\AjaxButton::widget([
                'label' => Yii::t('AdminModule.views_module_restrictInstallation', 'Save'),
                'ajaxOptions' => [
                    'type' => 'POST',
                    'beforeSend' => new yii\web\JsExpression('function(){ setModalLoader(); }'),
                    'success' => new yii\web\JsExpression('function(html){ $("#globalModal").html(html); }'),
                    'url' => \yii\helpers\Url::to(['/admin/module/restrict-installation', 'moduleId' => $module->id]),
                ],
                'htmlOptions' => [
                    'class' => 'btn btn-primary'
                ]
            ]);
            ?>
            <button type="button" class="btn btn-primary" data-dismiss="modal">
                <?= Yii::t('AdminModule.views_module_restrictInstallation', 'Close'); ?>
            </button>

            <?=
            \humhub\widgets\LoaderWidget::widget([
                'id' => 'default-loader',
                'cssClass' => 'loader-modal hidden'
            ]);
            ?>

        </div>

        <?php humhub\compat\CActiveForm::end(); ?>

    </div>
</div>

