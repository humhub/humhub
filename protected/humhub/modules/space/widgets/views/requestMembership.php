<div id="lightbox_requestWorkspace">

    <div class="panel panel_lightbox">
        <div class="content content_innershadow">

            <h2><?= Yii::t('SpaceModule.widgets_views_requestMembership', 'Request workspace membership'); ?></h2>

            <p><?= Yii::t('SpaceModule.widgets_views_requestMembership', 'Please shortly introduce yourself, to become a approved member of this workspace.'); ?></p>
            <br><br>

            <div class="form">

                <?php
                $form = $this->beginWidget('CActiveForm', [
                    'id' => 'workspace-apply-form',
                    'enableAjaxValidation' => true,
                ]);
                ?>

                <div class="row_lightbox">
                    <?= $form->labelEx($model, 'message'); ?>
                    <?= $form->textArea($model, 'message', ['rows' => '5', 'class' => 'textinput w310']); ?>
                    <?= $form->error($model, 'message'); ?>
                </div>

                <div class="clearFloats"></div>

                <div class="row_lightbox buttons">

                    <?= CHtml::ajaxButton(Yii::t('SpaceModule.widgets_views_requestMembership', 'Send'), ['workspace/requestMembershipForm', 'guid'=> $workspace->guid], [
                        'type' => 'POST',
                        'beforeSend' => 'function() {
                            jQuery("#loader_form_requestWorkspace").css("display", "block");
                        }',
                        'success' => 'function(html) {
                            jQuery("#lightbox_requestWorkspace").replaceWith(html);
                        }',
                    ], ['class' => 'input_button', 'id' => 'requestSubmit'.uniqid()]);
                    ?>

                    <?= CHtml::link(Yii::t('SpaceModule.widgets_views_requestMembership', 'Cancel'), '#', ['onclick'=>'RequestWorkspacebox.close()', 'class' => 'button', 'style' => 'color: #fff;']); ?>
                    <div id="loader_form_requestWorkspace" class="loader_form"></div>
                    <div class="clearFloats"></div>
                </div>

                <?php $this->endWidget(); ?>

            </div>
        </div>
    </div>
</div>
