<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\form\widgets;

use Yii;
use yii\bootstrap\InputWidget;


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
 * @since 1.6
 * @package humhub\modules\ui\form\widgets
 */
class SortOrderField extends InputWidget
{

    /**
     * @var int the default value
     */
    public $defaultValue = 100;

    /**
     * @inheritDoc
     */
    public function run()
    {
        $this->field->label(Yii::t('UiModule.form', 'Sort Order'));
        $this->field->hint(Yii::t('UiModule.form', 'Values between 0 and 10000, the existing elements usually use steps of 100.'));

        $model = $this->model;
        $attribute = $this->attribute;

        if ($this->defaultValue !== null && !is_numeric($model->$attribute)) {
            $model->$attribute = $this->defaultValue;
        }

        $this->options['type'] = 'number';
        return $this->renderInputHtml('text');
    }

}
