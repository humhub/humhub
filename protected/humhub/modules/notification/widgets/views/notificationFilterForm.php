<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\notification\models\forms\FilterForm;
use humhub\modules\ui\form\widgets\ActiveForm;

/* @var FilterForm $filterForm */
/* @var array $seenFilters */
?>
<?php $form = ActiveForm::begin([
    'id' => 'notification_overview_filter',
    'method' => 'GET',
    'options' => ['class' => 'form-checkboxes-normal'],
]) ?>

    <?= $form->field($filterForm, 'seenFilter')->radioList($seenFilters, ['template' => 'pills', 'wide' => true]) ?>

    <div style="padding-left:5px">
        <?= $form->field($filterForm, 'allFilter')->checkbox() ?>

        <?= $form->field($filterForm, 'categoryFilter')
            ->checkboxList($filterForm->getCategoryFilterSelection())
            ->label(false) ?>
    </div>

<?php ActiveForm::end() ?>
