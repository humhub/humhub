<?php

use humhub\libs\Html;
use humhub\modules\content\models\forms\AdminDeleteContentForm;
use humhub\modules\ui\form\widgets\ActiveForm;

/* @var $model AdminDeleteContentForm */

?>


<?php $form = ActiveForm::begin(['acknowledge' => true]); ?>

<?= $form->field($model, 'message')->textarea(['rows' => 3]) ?>
<?= $form->field($model, 'notify')->checkbox(['value' => '1', 'checked ' => true]) ?>

<?php ActiveForm::end(); ?>

<script <?= Html::nonce() ?>>
    var $messageTextarea = $('#admindeletecontentform-message');
    var $notifyCheckbox = $('#admindeletecontentform-notify');

    humhub.modules.ui.form.elements.initCheckbox($notifyCheckbox);

    $notifyCheckbox.on('change', function () {
        if($notifyCheckbox.is(':checked'))
            $messageTextarea.removeAttr('disabled');
        else
            $messageTextarea.attr('disabled', 'disabled');
    });
</script>
