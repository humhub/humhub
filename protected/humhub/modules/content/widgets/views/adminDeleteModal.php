<?php

use humhub\modules\content\models\forms\AdminDeleteContentForm;
use humhub\modules\ui\form\widgets\ActiveForm;

/* @var $model AdminDeleteContentForm */

?>


<?php $form = ActiveForm::begin(['acknowledge' => true]); ?>

<?= $form->field($model, 'message')->textarea(['rows' => 3]) ?>

<?php ActiveForm::end(); ?>
