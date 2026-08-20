<?php

namespace humhub\modules\content\widgets\richtext;

use humhub\helpers\Html;
use humhub\libs\ParameterEvent;
use humhub\modules\content\widgets\richtext\extensions\emoji\RichTextEmojiExtension;
use humhub\modules\content\widgets\richtext\extensions\file\FileExtension;
use humhub\modules\content\widgets\richtext\extensions\mentioning\MentioningExtension;
use humhub\modules\content\widgets\richtext\extensions\oembed\OembedExtension;
use humhub\modules\content\widgets\richtext\extensions\RichTextCompatibilityExtension;

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
 * Allows pasting of raw markdown content into the richtext editor.
 *
 * ### emoji
 * [twemoji](https://github.com/twitter/twemoji) and [markdown-it-emoji](https://www.npmjs.com/package/markdown-it-emoji) based emojies
 *
 * ### fullscreen
 * Adds a enlarge/shrink button to the rich text editor.
 *
 * ### mention
 * Markdown link extension for mentionings in the form of [<name>](mention:<guid> "<profile-url>").
 *
 * ### oembed
 * Enables scanning and replacement of pasted oembed links in form of link extensions [<url>](oembed:url)
 *
 * ### placeholder
 * Text placeholder for the editor input
 *
 * ### strikethrough
 * Markdown strikethrough formatting.
 *
 * ### table
 * Simple Markdown table support.
 *
 * ### upload
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
        RichTextEmojiExtension::class,
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->edit) {
            // In edit mode we only render a hidden rich text element
            $this->visible = false;
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $output = $this->getMarkdown();

        // E.g. when initializing empty editor
        if (empty($output)) {
            return $output;
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
     * The markdown text this richtext will render, after every extension's
     * {@see \humhub\modules\content\widgets\richtext\extensions\RichTextExtension::onBeforeOutput()} hook
     * has run (mention resolution, legacy-compat rewriting, ...) - i.e. exactly the text {@see self::run()}
     * itself HTML-encodes and wraps in the root div, extracted into its own method so
     * {@see self::getMarkdownAndRenderOptions()} (and any other caller needing the processed markdown
     * without the HTML envelope - see `CommentJsonService`) shares this SAME extension pipeline instead of
     * re-implementing it, and can never drift from what {@see self::run()} itself renders.
     *
     * No return type declared (matching `$this->text`'s own undeclared, effectively nullable
     * type) - `$this->text` can legitimately be `null` (e.g. an empty editor initialization
     * via `AbstractRichTextEditor::editOutput()`), and this preserves `run()`'s original,
     * pre-extraction behavior of returning it verbatim in that case rather than coercing to
     * `''` and risking a behavior change for callers upstream of `run()`.
     *
     * @return string|null
     * @since 1.19
     */
    public function getMarkdown()
    {
        $output = $this->text;

        // E.g. when initializing empty editor
        if (empty($output)) {
            return $output;
        }

        $this->trigger(self::EVENT_BEFORE_OUTPUT, new ParameterEvent(['output' => &$output]));

        foreach (static::getExtensions() as $extension) {
            $output = $extension->onBeforeOutput($this, $output);
        }

        return $output;
    }

    /**
     * The client-render counterpart of {@see self::run()}: instead of a pre-built HTML envelope string,
     * returns the processed markdown text (see {@see self::getMarkdown()}) plus the render options a
     * client-side `RichTextOutput.vue` needs to reproduce {@see self::run()}'s exact envelope div and any
     * per-record extension contributions client-side - see `docs/develop/ui-js-vuejs-interop.md`,
     * "RichTextOutput".
     *
     * `options` mirrors {@see self::getData()} (the SAME `data-*` attribute bucket {@see self::run()}'s own
     * envelope div carries, via {@see \humhub\widgets\JsWidget::getOptions()}) plus the `ui-widget`/`ui-init`
     * attributes {@see \humhub\widgets\JsWidget::setDefaultOptions()} adds (the only two of that method's
     * data contributions {@see \humhub\modules\content\widgets\richtext\ProsemirrorRichText} ever populates -
     * `widget-action-*`/`widget-fade-in`/`widget-reload-url` require `$events`/`$fadeIn`/`Reloadable` this
     * richtext never uses), plus every extension's own {@see \humhub\modules\content\widgets\richtext\extensions\RichTextExtension::getRenderOptions()}
     * contribution (empty for all but {@see \humhub\modules\content\widgets\richtext\extensions\oembed\OembedExtension}
     * today). The envelope's own auto-generated widget `id` is deliberately NOT included - it is a
     * per-render DOM-uniqueness counter with no semantic meaning client or theme CSS ever reads (the
     * client's own `[data-ui-richtext]` selector, not an id, is what locates richtext content - see
     * `humhub.ui.richtext.prosemirror.js`), so the client is free to mint its own.
     *
     * @return array{markdown: string, options: array}
     * @since 1.19
     */
    public function getMarkdownAndRenderOptions(): array
    {
        $markdown = $this->getMarkdown();

        if (empty($markdown)) {
            return ['markdown' => $markdown, 'options' => []];
        }

        $options = $this->getData();
        $options['ui-widget'] = $this->jsWidget;
        if (!empty($this->init)) {
            $options['ui-init'] = $this->init;
        }

        foreach (static::getExtensions() as $extension) {
            $options = array_merge($options, $extension->getRenderOptions());
        }

        return ['markdown' => $markdown, 'options' => $options];
    }
}
