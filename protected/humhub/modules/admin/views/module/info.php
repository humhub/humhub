<?php

use humhub\modules\content\widgets\richtext\converter\RichTextToHtmlConverter;
use yii\helpers\Html;

/* @var $name string */
/* @var $content string */
/* @var $description string */

?>
<div class="modal-dialog modal-dialog-normal animated fadeIn">
    <div class="modal-content">

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="myModalLabel">
                <?= Yii::t('AdminModule.modules', '<strong>Module</strong> details', ['%moduleName%' => Html::encode($name)]) ?>
            </h4>
        </div>
        <div class="modal-body">

            <div class="markdown-render" data-ui-richtext="1">
                <?php if (!empty($content)): ?>
                    <?= RichTextToHtmlConverter::process($content) ?>
                <?php else: ?>
                    <?= RichTextToHtmlConverter::process($description) ?>

                    <br>
                    <br>

                    <?= Yii::t('AdminModule.modules', 'This module doesn\'t provide further information.') ?>
                <?php endif; ?>
            </div>

        </div>

    </div>
</div>

