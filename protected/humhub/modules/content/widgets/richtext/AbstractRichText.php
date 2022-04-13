<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\content\widgets\richtext;

use humhub\libs\Html;
use humhub\modules\content\widgets\richtext\extensions\RichTextExtension;
use Yii;
use humhub\components\Event;
use humhub\widgets\JsWidget;
use yii\base\InvalidArgumentException;
use humhub\components\ActiveRecord;

/**
 * AbstractRichText serves as the base class for rich text implementations.
 *
 * A rich text Widget is used for rendering the output of a rich text and usually is related to an [[AbstractRichTextEditor]] implementation
 * defined by [[editorClass]].
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
    /**
     * @event Event an event raised after the post-process phase of the rich text.
     */
    public const EVENT_POST_PROCESS = 'postProcess';

    /**
     * @event \humhub\modules\search\events\ParameterEvent with parameter 'output'
     * @since 1.8
     */
    public const EVENT_BEFORE_OUTPUT = 'beforeOutput';

    /**
     * @event \humhub\modules\search\events\ParameterEvent with parameter 'output'
     * @since 1.8
     */
    public const EVENT_AFTER_OUTPUT = 'afterOutput';

    /**
     * Converter output format for html output
     * @since 1.8
     */
    public const FORMAT_HTML = 'html';

    /**
     * Converter output format for plaintext output
     * @since 1.8
     */
    public const FORMAT_PLAINTEXT = 'plaintext';

    /**
     * Short text format used in previews as notifications and activities.
     * @since 1.8
     */
    public const FORMAT_SHORTTEXT = 'shorttext';

    /**
     * Converter output format for markdown output
     * @since 1.8
     */
    public const FORMAT_MARKDOWN = 'markdown';

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
     *
     * @deprecated since 1.8 use shorttext converter instead
     */
    public $minimal = false;

    /**
     * @var int setting used to truncate the rich text content, usually related to [[minimal]] mode and used for previews
     *
     * @deprecated since 1.8 use shorttext converter instead
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
     * @var string [[AbstractRichTextConverter]] subclass, used for converting the richtext to other output formats
     * @since 1.8
     */
    protected static $converterClass;

    /**
     * @var mixed can be used to identify the related record
     */
    public $record;

    /**
     * @var array of richtext extension classes used for preparing and post processing output and converter result
     * @since 1.8
     */
    protected static $extensions = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if(!static::$editorClass) {
            throw new InvalidArgumentException('No editor class set for rich text '.static::class);
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
        $result = [];
        $original = $text;

        if(empty($text)) {
            $result['text'] = $text;
            return $result;
        }

        foreach (static::getExtensions() as $extension) {
            $text = $extension->onPostProcess($text, $record, $attribute, $result);
        }

        if($record && $attribute && $original !== $text) {
            $record->updateAttributes([$attribute => $text]);
        }

        $evt = new Event(['result' => array_merge($result, ['text' => $text, 'record' => $record, 'attribute' => $attribute])]);
        Event::trigger(static::class, static::EVENT_POST_PROCESS, $evt);

        return $evt->result;
    }

    /**
     * @param $extensionKey
     * @param $extensionClass
     * @return mixed
     * @since 1.8
     */
    public static function addExtension($extensionKey, $extensionClass)
    {
        return static::$extensions[$extensionKey] = $extensionClass;
    }

    /**
     * @return RichTextExtension[]
     * @since 1.8
     */
    public static function getExtensions()
    {
        $result = [];
        foreach (static::$extensions as $extension) {
            $result[] = call_user_func($extension.'::instance');
        }

        return $result;
    }

    /**
     * @return string renders the related [[AbstractRichTextEditor]] widget of this rich text implementation
     */
    public static function editorWidget($config = [])
    {
        return call_user_func(static::getEditorClass().'::widget', $config);
    }

    /**
     * @return AbstractRichTextConverter the related post-processor
     * @throws \yii\base\InvalidConfigException
     * @since 1.8
     */
    public static function getConverter() : AbstractRichTextConverter
    {
        return Yii::createObject(['class' => static::getConverterClass()]);
    }

    /**
     * @return string
     * @since 1.8
     */
    public static function getConverterClass() : string
    {
        return static::$converterClass;
    }

    /**
     * @return string
     */
    public static function getEditorClass() : string
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
    public static function output($text, $config = []) : string
    {
        $config['text'] = $text;
        return static::widget($config);
    }

    /**
     * Converts the richtext content to a given output format.
     *
     * The following formats are supported
     *
     * - 'plaintext': Translates the richtext to unencoded plain text
     * - 'markdown': Translates the richtext to plain markdown without specific richtext features
     * - 'html': Translates the richtext to HTML
     *
     * In case of 'html' you can switch from only supporting basic HTML (e.g. used for mails) to extended HTML support by
     * setting the 'minimal' option to true. The result may differ between different RichText implementations.
     *
     * @param string|null $content
     * @param string $format
     * @param array $options
     * @return string
     * @throws \yii\base\InvalidConfigException
     * @since 1.8
     * @see AbstractRichTextConverter
     */
    public static function convert(?string $content, $format = self::FORMAT_PLAINTEXT, $options = []) : string
    {
        $converter = static::getConverter();

        if ($content === null) {
            $content = '';
        }

        switch ($format) {
            case static::FORMAT_HTML:
                return $converter->convertToHtml($content, $options);
            case static::FORMAT_MARKDOWN:
                return $converter->convertToMarkdown($content, $options);
            case static::FORMAT_PLAINTEXT:
                return $converter->convertToPlaintext($content, $options);
            case static:: FORMAT_SHORTTEXT:
                return $converter->convertToShortText($content, $options);
            default:
                return Html::encode($converter->convertToPlaintext($content, $options));
        }
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
    public static function preview($text, $maxLength = 0, $config = []): string
    {
        if (!empty($maxLength)) {
            $config['maxLength'] = $maxLength;
        }
        return static::convert($text, static::FORMAT_SHORTTEXT, $config);
    }
}
