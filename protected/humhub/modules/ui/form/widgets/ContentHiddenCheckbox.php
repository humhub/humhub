<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\form\widgets;

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
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
    public const TYPE_CONTENT = 1;
    public const TYPE_CONTENTCONTAINER = 2;
    public const TYPE_GLOBAL = 3;

    /**
     * @var int
     */
    public int $type = self::TYPE_CONTENT;

    public array $hintOptions = ['tag' => 'p', 'class' => 'help-block'];

    /**
     * @inheritDoc
     */
    public function run()
    {
        $this->field->label(false);

        if ($this->type === self::TYPE_GLOBAL || $this->type === self::TYPE_CONTENTCONTAINER) {
            if (!isset($this->model->contentContainer)) {
                $this->options['label'] = Yii::t('UiModule.form', 'Hide all stream entries of this module globally by default');
                $this->hintOptions['hint'] = Yii::t('UiModule.form', 'Note: The default settings can be adjusted individually for each Space and each single stream entry. Hidden entries can be made visible using the stream filtering options.');
            } elseif ($this->model->contentContainer instanceof ContentContainerActiveRecord) {
                if ($this->model->contentContainer instanceof Space) {
                    $this->options['label'] = Yii::t('UiModule.form', 'Hide all stream entries in this Space by default');
                } elseif ($this->model->contentContainer instanceof User) {
                    $this->options['label'] = Yii::t('UiModule.form', 'Hide all stream entries in your Profile by default');
                }
                $this->hintOptions['hint'] = Yii::t('UiModule.form', 'Note: Hidden entries can be made visible using the stream filtering options. Single stream entries can be marked as visible on an individual level.');
            }
        } else {
            $this->options['label'] = Yii::t('UiModule.form', 'Hide stream entry');
        }

        return
            '<div class="checkbox">'
            . Html::activeCheckbox($this->model, $this->attribute, $this->options)
            . Html::activeHint($this->model, $this->attribute, $this->hintOptions)
            . '</div>';

    }

}
