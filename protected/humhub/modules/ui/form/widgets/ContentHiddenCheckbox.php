<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\form\widgets;

use Yii;
use yii\bootstrap\Html;
use yii\bootstrap\InputWidget;

/**
 * ContentHiddenCheckbox is the form field to set the Content Hidden flag.
 * Both default values and the actual content value are supported by this field.
 *
 * Mainly this field is used to provide a consistent label and hint across modules.
 *
 *  Example usage:
 *  ```
 *  <?= $form->field($model, 'contentHiddenDefault')->widget(ContentHiddenCheckbox::class, [
 *      'type' => ContentHiddenCheckbox::TYPE_CONTENTCONTAINER,
 *  ]); ?>
 *  ```
 *
 * @since 1.14
 */
class ContentHiddenCheckbox extends InputWidget
{
    const TYPE_CONTENT = 1;
    const TYPE_CONTENTCONTAINER = 2;
    const TYPE_GLOBAL = 3;

    /**
     * @var int
     */
    public int $type = self::TYPE_CONTENT;

    /**
     * @inheritDoc
     */
    public function run()
    {
        $this->field->label(false);

        if ($this->type === self::TYPE_GLOBAL || $this->type === self::TYPE_CONTENTCONTAINER) {
            $this->options['label'] = Yii::t('UiModule.form', 'Mark contents of this module as hidden in stream by default.');
        } else {
            $this->options['label'] = Yii::t('UiModule.form', 'Hide in Stream Overview');
        }

        return
            '<div class="checkbox">' .
            Html::activeCheckbox($this->model, $this->attribute, $this->options) .
            '</div';

    }

}
