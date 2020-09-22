<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\content\widgets\richtext;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This class together with the [[RichTextField]] class act as wrapper around the globally configured [[AbstractRichText]]
 * implementation and related [[AbstractRichTextEditor]] as well as [[AbstractRichTextProcessor]] by means of the configuration
 * parameter `richText`.
 *
 * This class should be used in favor of the actual [[AbstractRichText]] implementations in order to be able to
 * replace the default rich text on demand.
 *
 * A rich text editor field can be added to a form as follows:
 *
 * ```php
 * $form->field($model, 'richTextField')->widget(RichTextField::class);
 * ```
 *
 * This will render a rich text editor field related to the globally configured rich text implementation.
 * After submitting and saving the model record holding the rich text, the [[postProcess()]] function should be called
 * usually within the `afterSave()` function of the content record:
 *
 * ```php
 * RichText::postProcess($this->richTextField, $this);
 * ```
 * This will parse the rich text for features which require post-processing as _Mentionings_ or _Oembed_.
 *
 * The following line then can be used in a view to render the rich text output:
 *
 * ```php
 * RichText::output($model->richTextField);
 * ```
 *
 * A preview of the rich text can be rendered as follows:
 *
 * ```php
 * RichText::widget(['text' => $model->richTextField, 'minimal' => true, 'maxLength' => 60])
 * ```
 *
 * @author Julian Harrer <julian.harrer@humhub.com>
 * @since 1.2
 */
abstract class RichText extends AbstractRichText
{
    /**
     * Renders the rich text output by determining the configured rich text class.
     */
    public static function widget($config = [])
    {
        if(!isset($config['class'])) {
            $config = ArrayHelper::merge(Yii::$app->params['richText'], $config);
        }

        return call_user_func($config['class'].'::'.'widget', $config);
    }

    /**
     * @return string
     */
    public static function getProcessorClass()
    {
        return call_user_func(Yii::$app->params['richText']['class'].'::getProcessorClass');
    }

    /**
     * @return string
     */
    public static function getEditorClass()
    {
        return call_user_func(Yii::$app->params['richText']['class'].'::getEditorClass');
    }
}
