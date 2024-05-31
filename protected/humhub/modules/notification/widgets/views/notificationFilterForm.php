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
/* @var array $selectFilters */
/* @var array $seenFilters */
?>
<?php $form = ActiveForm::begin(['id' => 'notification_overview_filter', 'method' => 'GET']) ?>

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

    <div class="notification-filter-buttons">
        <?php foreach ($selectFilters as $value => $data) : ?>
            <?= Button::defaultType($data['title'])
                ->icon($data['icon'])
                ->options(['data-notification-filter-select' => $value])
                ->style($data['hidden'] ? ['display' => 'none'] : [])
                ->xs()
                ->loader(false) ?>
        <?php endforeach; ?>
    </div>

    <div style="padding:10px 0 0 5px">
        <?= $form->field($filterForm, 'categoryFilter')
            ->checkboxList($filterForm->getCategoryFilterSelection())
            ->label(false) ?>
    </div>

<?php ActiveForm::end() ?>
