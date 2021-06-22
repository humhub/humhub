<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\content\widgets\richtext;

use humhub\libs\EmojiMap;
use humhub\libs\Helpers;
use humhub\libs\ParameterEvent;
use humhub\modules\content\widgets\richtext\extensions\emoji\RichTextEmojiExtension;
use humhub\modules\content\widgets\richtext\extensions\file\FileExtension;
use humhub\modules\content\widgets\richtext\extensions\link\RichTextLinkExtensionMatch;
use humhub\modules\content\widgets\richtext\extensions\mentioning\MentioningExtension;
use humhub\modules\content\widgets\richtext\extensions\oembed\OembedExtension;
use humhub\modules\content\widgets\richtext\extensions\RichTextCompatibilityExtension;
use humhub\modules\content\widgets\richtext\extensions\link\RichTextLinkExtension;
use yii\helpers\Html;

/**
 * The ProsemirrorRichText is a [Prosemirror](https://prosemirror.net) and [Markdown-it](https://github.com/markdown-it/markdown-it)
 * based rich text implementation.
 *
 * This rich text is a pure markdown based rich text enhanced with some additional features and markdown plugins.
 *
 * In order to stay compatible with the legacy rich text content, this rich text contains some pre-processing logic on the server side, which can be deactivated
 * if not required by means of the `richtextCompatMode` setting of the `content` module.
 *
 * Note that this rich text, when in edit mode, just outputs an invisible div with pure markdown content, which will be interpreted by
 * the related ProsemirrorRichTextEditor.
 *
 * This rich text implementation supports all features as [[preset]], the [[includes]] and [[excludes]] of plugins
 * and is extensible through additional javascript plugins.
 *
 * Note that the plugin settings as [[preset]], [[includes]], [[excludes]], [[pluginOptions]] have to be set for the editor as
 * well as for the rich text output widget.
 *
 * Beside the default (GFM based) markdown-it syntax, the following plugins are available:
 *
 * ### anchors
 *
 * If enabled will add anchors to heading elements. This plugin is disabled by default and can be enabled as follows:
 *
 * ```php
 * RichText::output($text, [
 *     'preset' => 'myPreset',
 *     'pluginOptions' => [
 *         'anchors' => true
 *     ]
 * ]);
 *
 * // or with specific settings
 * RichText::output($text, [
 *     'preset' => 'myPreset',
 *     'pluginOptions' => [
 *         'anchors' => ['permalink' => true]
 *     ]
 * ]);
 * ```
 * See [markdown-it-anchor](https://www.npmjs.com/package/markdown-it-anchor) for more settings.
 *
 * ### clipboard
 *
 * Allows pasting of raw markdown content into the richtext editor.
 *
 * ### emoji
 *
 * [twemoji](https://github.com/twitter/twemoji) and [markdown-it-emoji](https://www.npmjs.com/package/markdown-it-emoji) based emojies
 *
 * ### fullscreen
 *
 * Adds a enlarge/shrink button to the rich text editor.
 *
 * ### mention
 *
 * Markdown link extension for mentionings in the form of [<name>](mention:<guid> "<profile-url>").
 *
 * ### oembed
 *
 * Enables scanning and replacement of pasted oembed links in form of link extensions [<url>](oembed:url)
 *
 * ### placeholder
 *
 * Text placeholder for the editor input
 *
 * ### strikethrough
 *
 * Markdown strikethrough formatting.
 *
 * ### table
 *
 * Simple Markdown table support.
 *
 * ### upload
 *
 * File upload support.
 *
 * @author Julian Harrer <julian.harrer@humhub.com>
 * @see https://github.com/humhub/humhub-prosemirror for more information about the prosemirror-richtext client implementation
 * @see https://prosemirror.net/docs/ Prosemirror documentation
 * @see https://github.com/markdown-it/markdown-it markdown-it repository
 * @since 1.3
 */
class ProsemirrorRichText extends AbstractRichText
{
    /**
     * @inheritdoc
     */
    public $jsWidget = 'ui.richtext.prosemirror.RichText';

    /**
     * @inheritdoc
     */
    protected static $editorClass = ProsemirrorRichTextEditor::class;

    /**
     * @inheritdoc
     */
    protected static $converterClass = ProsemirrorRichTextConverter::class;

    /**
     * @var string[]
     * @since 1.8
     */
    protected static $extensions = [
        RichTextCompatibilityExtension::class,
        MentioningExtension::class,
        FileExtension::class,
        OembedExtension::class,
        RichTextEmojiExtension::class
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if($this->edit) {
            // In edit mode we only render a hidden rich text element
            $this->visible = false;
        }
    }

    /**
     * @inheritdoc
     */
    public function run() {
        if($this->minimal) {
            return static::convert($this->text, static::FORMAT_SHORTTEXT, ['maxLength' => $this->maxLength]);
        }

        $output = $this->parseOutput();

        // E.g. when initializing empty editor
        if(empty($output)) {
            return $output;
        }

        $this->trigger(self::EVENT_BEFORE_OUTPUT, new ParameterEvent(['output' => &$output]));

        foreach (static::getExtensions() as $extension) {
            $output = $extension->onBeforeOutput($this, $output);
        }

        // Can be removed in a future version, richtext should not be cut anymore, only text or shorttext version should be cut
        if ($this->maxLength > 0) {
            $output = Helpers::truncateText($output, $this->maxLength);
        }

        // Wrap encoded output in root div
        $this->content = Html::encode($output);
        $output = parent::run();

        foreach (static::getExtensions() as $extension) {
            $output = $extension->onAfterOutput($this, $output);
        }

        $this->trigger(self::EVENT_AFTER_OUTPUT, new ParameterEvent(['output' => &$output]));

        return trim($output);
    }

    /**
     * Prior of 1.8 this function was used for preparing the richtext output. In 1.8 we richtext extensions should be
     * used to manipulate the richtext output.
     *
     * @return string
     * @deprecated since 1.8 use `RichTextExtension::onBeforeOutput()` to manipulate output
     */
    protected function parseOutput() {
        return $this->text;
    }

    /**
     * Can be used to scan for link extensions of the form [<text>](<extension>:<url> "<title>") in which the actual meaning
     * of the placeholders is up to the extension itself.
     *
     * @param $text string rich text content to parse
     * @param $extension string|null extension string if not given all extension types will be included
     * @return array
     * @deprecated since 1.8 use `ProsemirrorRichTextConverter::scanLinkExtension()`
     */
    public static function scanLinkExtension($text, $extension = null)
    {
        $matches = [];
        $result = RichTextLinkExtension::scanLinkExtension($text, $extension);
        foreach ($result as $match) {
            $matches[] = $match->match;
        }

        return $matches;
    }

    /**
     * Can be used to scan and replace link extensions of the form [<text>](<extension>:<url> "<title>") in which the actual meaning
     * of the placeholders is up to the extension itself.
     *
     * @param string|null $text string rich text content to parse
     * @param string|null $extension extension string if not given all extension types will be included
     * @param callable $callback
     * @return mixed
     * @deprecated since 1.8 use `ProsemirrorRichTextConverter::replaceLinkExtension()`
     */
    public static function replaceLinkExtension(?string $text, ?string $extension, callable $callback)
    {
        return RichTextLinkExtension::replaceLinkExtension($text, $extension, function (RichTextLinkExtensionMatch $match) use ($callback) {
            return $callback($match->match);
        });
    }
}
