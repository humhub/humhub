<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\notification\models\forms\FilterForm;
use humhub\widgets\Button;
use yii\bootstrap\ActiveForm;

/* @var FilterForm $filterForm */
/* @var array $seenFilters */
?>
<?php $form = ActiveForm::begin([
    'id' => 'notification_overview_filter',
    'method' => 'GET',
    'options' => ['class' => 'form-checkboxes-normal'],
]) ?>

    <div class="notification-filter-buttons">
        <?php foreach ($seenFilters as $value => $data) : ?>
            <?= Button::defaultType($data['title'])
                ->icon($data['icon'])
                ->options(['data-notification-filter-seen' => $value])
                ->cssClass($data['active'] ? 'active' : '')
                ->xs()
                ->loader(false) ?>
        <?php endforeach; ?>
    </div>
    <?= $form->field($filterForm, 'seenFilter')->hiddenInput()->label(false) ?>

    <div style="padding-left:5px">
        <?= $form->field($filterForm, 'allFilter')->checkbox() ?>

        <?= $form->field($filterForm, 'categoryFilter')
            ->checkboxList($filterForm->getCategoryFilterSelection())
            ->label(false) ?>
    </div>

<?php ActiveForm::end() ?>
