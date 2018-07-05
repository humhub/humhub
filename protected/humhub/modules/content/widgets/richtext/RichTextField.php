<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\content\widgets\richtext;

use Yii;

/**
 * The RichTextField widget will render a rich text input element.
 *
 * This class acts as wrapper around the actual [[AbstractRichTextEditor]] implementation, which can either be set
 * by the widget configuration `class` or globally by the `richText` configuration parameter.
 *
 * The following line adds a rich text input field rendered by the globally configured rich text to a form:
 *
 * ```php
 * $form->field($model, 'richTextField')->widget(RichTextField::class);
 * ```
 *
 * @author Julian Harrer <julian.harrer@humhub.com>
 * @see RichText for more information about the usage of rich texts
 * @author buddha
 */
class RichTextField extends AbstractRichTextEditor
{
    /**
     * @inheritdoc
     */
    public static function widget($config = [])
    {
        if(!isset($config['class'])) {
            $richtextClass = Yii::$app->params['richText']['class'];
            return call_user_func($richtextClass.'::editorWidget', $config);
        }

        return parent::widget($config);
    }
}
