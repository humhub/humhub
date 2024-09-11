<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\form\widgets;

/**
 * SortOrderField is a uniform form field for setting a numeric sort order for model classes.
 *
 * The label and hint text is set automatically and it is not necessary to implement a attributeLabel or attributeHint
 * in the model class.
 *
 * Future implementations of this class could also output a slider (or similar) instead of a text input field.
 *
 * Example usage:
 *
 * ```php
 * <?= $form->field($model, $attribute)->widget(SortOrderField::class, [
 *     // configure additional widget properties here
 * ]) ?>
 * ```
 *
 * @deprecated since 1.17
 * @since 1.6
 * @package humhub\modules\ui\form\widgets
 */
class SortOrderField extends \humhub\widgets\bootstrap\SortOrderField
{
}
