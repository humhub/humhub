<?php

use humhub\modules\comment\models\forms\AdminDeleteCommentForm;
use humhub\modules\ui\form\widgets\ActiveForm;

/* @var $model AdminDeleteCommentForm */

?>


<?php $form = ActiveForm::begin(['acknowledge' => true]); ?>

<?= $form->field($model, 'message')->textarea(['rows' => 3]) ?>

<?php ActiveForm::end(); ?>
