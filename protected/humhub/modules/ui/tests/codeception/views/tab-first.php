<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\widgets\form\ActiveForm;
use tests\codeception\models\TestTabbedFormModel;

/* @var $form ActiveForm */
/* @var $tabbedForm TestTabbedFormModel */
?>

<?= $form->field($tabbedForm, 'firstname')->textInput() ?>
<?= $form->field($tabbedForm, 'lastname')->textInput() ?>
<?= $form->field($tabbedForm, 'email')->textInput() ?>
