<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\content\widgets\richtext;

use humhub\modules\ui\form\widgets\JsInputWidget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Abstract class for RichTextEditor implementations.
 *
 * Most RichTextEditor fields will use some kind of contenteditable element in combination with an underlying input field.
 *
 * A RichTextEditor feature set may vary between implementations, and is ideally configurable for a single instance through
 * plugin settings like:
 *
 *  - `$preset`: select a preset as 'markdown', 'normal', 'full' or a custom preset provided by an other module
 *  - `$plugins`: set plugin options
 *  - `§includes`: include some additional plugins
 *  - `$exclude`: exclude some specific plugins
 *
 * Some common plugin extensions are
 *  - placeholder
 *  - mention
 *  - oembed
 *  - emoji
 *
 * This abstract class provides direct settings of some core plugins as `$placeholder` and `$mentionUrl`.
 * Other plugin settings can be configured by means of the `$plugins` array.
 *
 * To render the RichtText output for a given plain text use the static `output()` function, which internally will determine the configured
 * `RichText` class to transform the text into the output format required by the RichText e.g. Markdown or directly HTML.
 *
 * > Note: the `output()` function by default is also used in editor edit mode with the `edit` flag set to true.
 * > Note: Some Richtext implementation may not support all mentioned features and plugins.
 *
 * @author Julian Harrer <julian.harrer@humhub.com>
 * @since 1.3
 */
class AbstractRichTextEditor extends JsInputWidget
{
    const LAYOUT_BLOCK = 'block';

    const LAYOUT_INLINE = 'inline';

    /**
     * @var string richtext feature preset e.g: 'markdown', 'normal', 'full'
     */
    public $preset;

    /**
     * @var string defines the style/layout of the richtext
     */
    public $layout = self::LAYOUT_BLOCK;

    /**
     * Can be used to overwrite the default placeholder.
     *
     * @var string
     */
    public $placeholder;

    /**
     * The url used for the default @ metioning.
     * If there is no $searchUrl is given, the $searchRoute will be used instead.
     *
     * @var string
     */
    public $mentioningUrl;

    /**
     * Route used for the default @ mentioning. This will only be used if
     * not $searchUrl is given.
     *
     * @var string
     */
    protected $mentioningRoute = "/search/search/mentioning";

    /**
     * RichText plugin supported for this instance.
     * By default all features will be included.
     *
     * @var array
     */
    public $include = [];

    /**
     * RichText plugins not supported in this instance.
     * This can also be used do exclude specific plugins if not supported by the RichText implementation.
     *
     * @var array
     */
    public $exclude = [];

    /**
     * Additional pluginoptions
     * @var array
     */
    public $pluginOptions = [];

    /**
     * If set to true the picker will be focused automatically.
     *
     * @var boolean
     */
    public $focus = false;

    /**
     * Disables the input field.
     * @var boolean
     */
    public $disabled = false;

    /**
     * Will be used as user feedback, why this richtext is disabled.
     *
     * @var string
     */
    public $disabledText = false;

    /**
     * @inheritdoc
     */
    public $init = true;

    /**
     * @inheritdoc
     */
    public $visible = true;

    /**
     * @var boolean defines if the default label should be rendered.
     */
    public $label = false;

        /**
         * @inhertidoc
         */
    public function run()
    {
        $inputOptions = $this->getInputAttributes();

        if ($this->form != null) {
            $input = $this->form->field($this->model, $this->attribute)->textarea($inputOptions)->label(false);
            $richText = Html::tag('div', $this->editOutput($this->getValue()), $this->getOptions());
            $richText = $this->getLabel() . $richText;
        } elseif ($this->model != null) {
            $input = Html::activeTextarea($this->model, $this->attribute, $inputOptions);
            $richText = Html::tag('div', $this->editOutput($this->getValue()), $this->getOptions());
            $richText = $this->getLabel() . $richText;
        } else {
            $input = Html::textarea(((!$this->name) ? 'richtext' : $this->name), $this->value, $inputOptions);
            $richText = Html::tag('div', $this->editOutput($this->getValue()), $this->getOptions());
            $richText = $this->getLabel() . $richText;
        }

        return $input . $richText . $this->prepend();
    }

    /**
     * @var [] renderer class definition
     */
    public static $renderer;

    /**
     * This method can be overwritten in order to prepend content after the actual rich text content.
     * @return string
     */
    public function prepend() {
        return '';
    }

    /**
     * @return array attributes added to the hidden textarea input of the richtext
     */
    public function getInputAttributes()
    {
        return [
            'id' => $this->getId(true) . '_input',
            'style' => 'display:none;',
            'title' => $this->placeholder
        ];
    }

    /**
     * @return bool|string returns the html label used for rendering
     */
    public function getLabel()
    {
        if(!$this->label) {
            return "";
        }

        if ($this->label === true && $this->model != null) {
            return Html::activeLabel($this->model, $this->attribute, ['class' => 'control-label']);
        }

        return $this->label;
    }

    /**
     * Returns the content formatted for editing by means of the configured [[renderer]].
     *
     * This function will call [[RichText::output()]] with given richtext settings and `edit = true`.
     *
     * @param $content
     * @return string
     * @internal param array $params
     */
    protected function editOutput($content)
    {
        $params = [
            'edit' => true,
            'exclude' => $this->exclude,
            'include' => $this->include,
            'pluginOptions' => $this->pluginOptions,
            'preset' => $this->preset,
        ];

        $config = ArrayHelper::merge(static::$renderer, $params);
        unset($config['class']);
        return call_user_func(static::$renderer['class'].'::output', $content, $config);
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        $result = [
            'exclude' => $this->exclude,
            'include' => $this->include,
            'mentioning-url' => $this->getMentioningUrl(),
            'placeholder' => $this->placeholder,
            'plugin-options' => $this->pluginOptions,
            'preset' => $this->preset,
            'focus' => $this->focus
        ];

        if ($this->disabled) {
            $result['disabled'] = true;
            $result['disabled-text'] = $this->disabledText;
        }

        return $result;
    }

    /**
     * @return string returns the url used by the mention plugin
     */
    public function getMentioningUrl()
    {
        return ($this->mentioningUrl) ? $this->mentioningUrl : Url::to([$this->mentioningRoute]);
    }
}
