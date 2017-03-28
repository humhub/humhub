<?php

use yii\helpers\Html;
?>
<div class="modal-dialog modal-dialog-normal animated fadeIn">
    <div class="modal-content">

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="myModalLabel">
                <?= Yii::t('AdminModule.views_module_info', '<strong>Module</strong> details', ['%moduleName%' => Html::encode($name)]); ?>
            </h4>
        </div>
        <div class="modal-body">

            <div class="markdown-render">
                <?php if ($content != ""): ?>

                    <?= \yii\helpers\Markdown::process($content); ?>

                <?php else: ?>
                    <?= $description; ?>
                    <br>
                    <br>

                    <?= Yii::t('AdminModule.views_module_info', 'This module doesn\'t provide further informations.'); ?>
                <?php endif; ?>
            </div>

        </div>

    </div>
</div>

