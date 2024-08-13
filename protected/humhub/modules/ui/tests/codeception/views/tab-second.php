<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\ui\form\widgets\ActiveForm;
use tests\codeception\models\TestTabbedFormModel;

/* @var $form ActiveForm */
/* @var $tabbedForm TestTabbedFormModel */
?>

<?= $form->field($tabbedForm, 'countryId')->textInput() ?>
<?= $form->field($tabbedForm, 'stateId')->textInput() ?>
<?= $form->field($tabbedForm, 'cityId')->textInput() ?>
