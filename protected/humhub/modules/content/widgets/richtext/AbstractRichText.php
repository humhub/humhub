<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\content\widgets\richtext;

use Yii;
use humhub\components\Event;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\widgets\JsWidget;
use yii\base\InvalidArgumentException;
use yii\db\ActiveRecord;

/**
 * AbstractRichText serves as the base class for rich text implementations.
 *
 * A rich text Widget is used for rendering the output of a rich text and usually is related to an [[AbstractRichTextEditor]] implementation
 * defined by [[editorClass]] and an [[AbstractRichTextProcessor]] implementation for post-processing rich texts defined by [[processorClass]].
 *
 * In order for some features as the _Mentionings_ to work a rich text should only be used for [[ContentActiveRecord]] or [[ContentAddonActiveRecord]] models.
 *
 * A rich text can implement the following core features:
 *
 *  - [[preset]] defines a preset of plugins and settings
 *  - [[include]] and **exclude** specific plugins
 *  - [[pluginOptions]] configure specific plugins
 *  - [[minimal]] rendering mode used e.g. for activities and mails, should truncate the input by means of the [[maxLenght]] option
 *  - [[edit]] rendering mode used for rendering the rich text in a format interpretable by the editor
 *
 * Common rich text plugins are _Mentionings_, _Oembed_ and text formatting features.
 *
 * It's up to the implementation which features or plugins are supported.
 *
 * After saving the related record of a rich text the [[postProcess()]] function should be called manually in order to
 * parse the rich text e.g. for _Mentionings_ or other features etc.
 * This usually happens in the [[ContentActiveRecord::afterSave()]] function of the record.
 *
 * Furthermore the [[output()]] function can be used as convenience function to render a given text.
 *
 * ```php
 * $richText = RichText::output($text, $options) ?>
 * ```
 *
 * > Note: Subclasses provided by third party modules should ideally be compatible with the default implementation in
 * order to be able to switch the RichText implementation without loosing the semantic.
 *
 * @author Julian Harrer <julian.harrer@humhub.com>
 * @since 1.3
 */
abstract class AbstractRichText extends JsWidget
{
    const PRESET_DOCUMENT = 'document';

    /**
     * @event Event an event raised after the post-process phase of the rich text.
     */
    const EVENT_POST_PROCESS = 'postProcess';

    /**
     * @event \humhub\modules\search\events\ParameterEvent with parameter 'output'
     */
    const EVENT_BEFORE_OUTPUT = 'beforeOutput';

    /**
     * @var string defines a preset of rich text features and settings
     * @see AbstractRichTextEditor::$preset
     */
    public $preset;

    /**
     * @inheritdoc
     */
    public $init = true;

    /**
     * @var string text to display
     */
    public $text = "";

    /**
     * @var boolean
     */
    public $encode = true;

    /**
     * @var boolean enables the edit rendering mode
     */
    public $edit = false;

    /**
     * @var boolean enables the minimal rendering mode used for example for previews, this mode should take the
     * [[maxLenght]] setting into account for truncating the preview content.
     */
    public $minimal = false;

    /**
     * @var int setting used to truncate the rich text content, usually related to [[minimal]] mode and used for previews
     */
    public $maxLength = 0;

    /**
     * @var boolean defines if this rich text is also used as client side markdown text.
     * @deprecated since 1.3
     */
    public $markdown = false;

    /**
     * @var array Can be used to explicitly include specific plugins in addition to the set of defaults (preset)
     */
    public $include = [];

    /**
     * @var array Can be used to exclude specific plugins from the set of defaults (preset)
     */
    public $exclude = [];

    /**
     * @var array rich text plugin settings. Note that changes of those settings may require an additional preset.
     */
    public $pluginOptions = [];

    /**
     * @var string [[AbstractRichTextEditor]] subclass, used for rendering the editor widget.
     * @see editorWidget
     */
    protected static $editorClass;

    /**
     * @var string [[AbstractRichTextProcessor]] subclass, used for post-processing the rich text content
     * @see postProcess
     */
    protected static $processorClass;

    /**
     * @var mixed can be used to identify the related record
     */
    public $record;

    /**
     * @inheritdoc
     */
    public function init() {
        parent::init();
        if(!static::$editorClass || ! static::$processorClass) {
            throw new InvalidArgumentException('No editor or processor class set for rich text '.static::class);
        }
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        $result = [
            'exclude' => $this->exclude,
            'include' => $this->include,
            'plugin-options' => $this->pluginOptions,
            'edit' => $this->edit,
            'ui-richtext' => true
        ];

        if(!empty($this->preset)) {
            $result['preset'] = $this->preset;
        }

        return $result;
    }

    /**
     * Used for the post-processing of the rich text, normally called within [[ContentActiveRecord::afterSave()]]
     * of the related [[ContentActiveRecord]].
     *
     * @param $text string RichText content
     * @param ActiveRecord $record
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public static function postProcess($text, $record, $attribute = null)
    {
        $processor = static::getProcessor($text,$record, $attribute);
        $processorResult = $processor->process();

        if($record && $attribute && $processor->text !== $text) {
            $record->updateAttributes([$attribute => $processor->text]);
        }

        Event::trigger(static::class, static::EVENT_POST_PROCESS, new Event(['data' => ['processorClass' => static::$processorClass, 'text' => $text, 'record' => $record]]));
        return $processorResult;
    }

    /**
     * @return string renders the related [[AbstractRichTextEditor]] widget of this rich text implementation
     */
    public static function editorWidget($config = [])
    {
        return call_user_func(static::getEditorClass().'::widget', $config);
    }


    /**
     * @param $text string rich text content to be processed
     * @param $record ActiveRecord related model holding the rich text
     * @return AbstractRichTextProcessor the related post-processor
     * @throws \yii\base\InvalidConfigException
     */
    public static function getProcessor($text, $record, $attribute = null)
    {
        return Yii::createObject([
            'class' => static::getProcessorClass(),
            'text' => $text,
            'attribute' => $attribute,
            'record' => $record]);
    }

    /**
     * @return string
     */
    public static function getProcessorClass()
    {
        return static::$processorClass;
    }

    /**
     * @return string
     */
    public static function getEditorClass()
    {
        return static::$editorClass;
    }

    /**
     * Renders the given text by means of the given config.
     *
     * This acts as convenience method for [[widget()]].
     *
     * @param $text string rich text content to be rendered
     * @param array $config rich text widget options
     * @return string render result
     * @throws \Exception
     */
    public static function output($text, $config = [])
    {
        $config['text'] = $text;
        return static::widget($config);
    }

    /**
     * Renders the given text in minimal render mode truncated by the `maxLength` parameter.
     *
     * This acts as convenience method for [[widget()]].
     *
     * @param $text string rich text content to be rendered
     * @param $maxLength int max length of the preview
     * @param array $config rich text widget options
     * @return string render result
     * @throws \Exception
     */
    public static function preview($text, $maxLength = 0)
    {
        $config['maxLength'] = $maxLength;
        $config['minimal'] = true;
        return static::output($text, $config);
    }
}
