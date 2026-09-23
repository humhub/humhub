# Rich text

Content a user writes with formatting — a post, a comment, a space's about text, a wiki page —
is **rich text**: markdown with a few platform-specific additions, written in a
ProseMirror-based editor and rendered to HTML in the browser. This page describes the format,
the pipelines a text goes through (input, post-processing, output, conversion) and the two
extension points: PHP **extensions**, which process the text on the server, and JavaScript
**plugins**, which edit and render it in the browser.

Everything server-side lives in `humhub\modules\content\widgets\richtext`. `RichText` is the
static façade every caller uses; it dispatches to the implementation configured in
`Yii::$app->params['richText']['class']` (default `ProsemirrorRichText`, with
`ProsemirrorRichTextEditor` as its editor). Client-side there are two parts: the
`humhub-prosemirror-richtext` package (bundled as `humhub-editor.js`, exposed as
`humhub.richtext` and as the `ui.richtext` module), which holds the editor, the markdown
renderer and every plugin, and the `ui.richtext.prosemirror` module
(`humhub.ui.richtext.prosemirror.js`) with the two widgets the platform boots on markup:
`RichText` (display) and `RichTextEditor`.

## The format

What is stored, and what the [HTTP API](concept-api.md) ships, is **HumHub richtext markdown**:
CommonMark with tables and strikethrough, plus these additions the platform resolves itself.

| Syntax | Meaning |
|---|---|
| `[Jane Doe](mention:<guid> "<profile url>")` | Mention of a user or space. Text is the display name, title the profile URL. |
| `[report.pdf](file-guid:<guid> "<file url>")`, `![alt](file-guid:<guid> "<file url>")` | An attached file, or an image of one. |
| `[<url>](oembed:<url>)` | A link that renders as an embedded preview when an [oEmbed provider](advanced-oembed.md) matches. |
| `:smile:` | Emoji shortcode. |

The scheme in front of the colon is the **key** of the extension that owns the link
(`RichTextLinkExtension::$key`); `buildExtensionLink()` builds such a link and
`scanLinkExtension()` finds them. Name and URL inside a mention or file link are a snapshot:
the platform re-resolves them on output (see below), the snapshot keeps the text readable and
gives a plain markdown renderer something to show.

Texts written before HumHub 1.3 use an older syntax (`;smile;` emoji, `@-<guid>` mentions,
bare URLs for embeds). `RichTextCompatibilityExtension` rewrites them on the fly whenever a text
is output or converted. The rewrite is switched off by the content module's
`richtextCompatMode` property or db setting of the same name once no legacy content is left.

Two properties of the format matter for everything that follows:

- It is the **canonical, lossless** representation. Converted forms (plain markdown, HTML) drop
  the guid bindings; a client that edits a text therefore always works on this format and sends
  it back unchanged in structure.
- Rendering it needs the platform: three URL schemes and the emoji shortcodes have no meaning
  to a generic markdown renderer. HumHub's own renderer runs in the browser (the display
  widget); a third-party client needs a link hook for the three schemes.

## Lifecycle of a text

```
   editor (ProseMirror) ──serialize──▶ richtext markdown ──save──▶ record
                                                                      │
                            afterSave(): RichText::postProcess() ◀────┘
                            attach files, notify mentions, preload oembed previews
                                                │
        ┌───────────────────────────────────────┼─────────────────────────────────────┐
        ▼                                       ▼                                     ▼
 RichText::output()               RichText::outputMarkdown()               RichText::convert()
 envelope with the markdown       processed markdown, caller-neutral       html · markdown · plaintext ·
 + hidden oembed previews         (API payloads)                           short_text · short_html
        │                                       │                          mails, notifications, search
        ▼                                       ▼
 display widget renders           RichTextOutput.vue loads the oembed
 markdown → HTML in the browser   previews, then boots the same display widget
```

### Input: the editor

In an `ActiveForm` the field widget is `RichTextField`:

```php
<?= $form->field($model, 'message')->widget(RichTextField::class, [
    'placeholder' => Yii::t('MyModule.base', 'Write something…'),
    'preset' => 'full',
    'pluginOptions' => ['maxHeight' => 300],
]) ?>
```

Outside a form, `ProsemirrorRichTextEditor::widget([...])` renders the same editor. The options
that shape it: `preset`, `include`, `exclude` and `pluginOptions` select and configure the
client plugins (see [Plugins](#plugins-javascript)); `placeholder`, `layout` (block or inline
menu), `focus`, `disabled`/`disabledText`, `label` and `fieldOptions` shape the field;
`mentioningUrl` (default route `/user/mentioning`) is the search the mention autocomplete
queries; `backupInterval` (seconds, default 3) drives the draft backup in `sessionStorage`,
keyed by the field's `id` — set an explicit `id` when a draft should survive a reload.

Server-side the editor renders the current value through `RichText::output()` with
`edit = true` — a hidden `[data-ui-richtext]` element holding the raw markdown, so the same
extension processing applies as for display — plus a hidden `<textarea>` (`<id>_input`) that
carries the value into the form. The `RichTextEditor` widget boots a ProseMirror
`MarkdownEditor` on it, reads the initial markdown from the hidden element (or from the backup),
and writes the serialized markdown back into the textarea on focusout and on `clear`. Mention
autocomplete, file upload, paste and drop, the emoji picker, a link paste turning into an
embedded preview when a provider matches, and a source (markdown) mode are plugins of the
editor bundle. Inside a Vue island the same editor is hosted by the form suite's
`RichTextField` component, see [Vue.js form suite](ui-js-vuejs-forms.md).

### Post-processing: after save

The text a form submits is not final yet. A record that owns a rich text attribute calls
`RichText::postProcess($text, $record, $attribute)` in its `afterSave()` (`Post` and `Comment`
do; the space manage controller does it for the about text). Every extension's
`onPostProcess()` runs over the text:

- `FileExtension` attaches every referenced `file-guid:` file to the record (via its
  `fileManager`) and converts pasted base64 images into files first. Files cannot be attached
  to an unpersisted record, which is why this runs after save.
- `MentioningExtension` creates the `Mentioning` records — the source of the mention
  notifications.
- `OembedExtension` preloads the previews of embedded links and lists them in
  `$result['oembed']`.

A hook may rewrite the text; if it changed, `postProcess()` stores it with
`updateAttributes()`. Finally `AbstractRichText::EVENT_POST_PROCESS` fires with the collected
`result` (text, record, attribute and every extension's entries).

### Output for an HTML page

`RichText::output($text, ['record' => $model])` is what views and stream entries call. It does
not produce HTML from the markdown — that happens in the browser — but prepares the text and
wraps it:

1. `ProsemirrorRichText::getMarkdown()`: fires `EVENT_BEFORE_OUTPUT` (a `ParameterEvent` whose
   `output` a handler may change), then every extension's `onBeforeOutput()`. The
   compatibility extension rewrites legacy syntax, `MentioningExtension` rebuilds every mention
   with the container's **current** display name and URL (or marks it "not found" for a
   deactivated user). The result is the processed markdown, identical for every reader.
2. The text is HTML-encoded and wrapped in the envelope `humhub\widgets\JsWidget` renders:
   `<div data-ui-richtext data-ui-widget="ui.richtext.prosemirror.RichText" data-ui-init
   data-preset="…" data-include="…" data-exclude="…" data-plugin-options="…">`. Inside is still
   markdown, as text.
3. Every extension's `onAfterOutput()` may append markup: `OembedExtension` fetches the
   previews of the embedded links for the current user (`UrlOembed::getOEmbed()`, which
   returns the consent prompt for a domain the user has not allowed) and appends them as a
   hidden `.richtext-oembed-container` sibling with one `[data-oembed="<url>"]` element per
   link. Then `EVENT_AFTER_OUTPUT` fires, and `yii\base\Widget::EVENT_AFTER_RUN` as for any
   widget.
4. In the browser, `ui.additions` boots the `RichText` display widget on the envelope. It
   renders the markdown with markdown-it and the plugins the preset selects, replaces the
   element's content with the HTML, marks it `data-ui-richtext-rendered`, applies the
   additions that enhance rendered content (code highlighting, …) and triggers the jQuery
   event `afterRender` on the element. The oembed plugin's renderer asks `humhub.oembed.js`
   `get(url)` for the preview, which finds it in the hidden container.

### Output for the API and Vue islands

`RichText::outputMarkdown($text, ['record' => $model])` is step 1 alone: the processed markdown,
no envelope, no previews. It is deliberately **caller-neutral** — nothing in it depends on who
asks — so an API payload built from it can be cached and served to every reader alike (see
[HTTP API framework](concept-api.md)). The Vue component `RichTextOutput` renders such a
payload: it collects the `oembed:` links of the message, loads their previews through
`humhub.oembed.js` `load(urls, {consent: true})` (which honors the viewer's consent) and then
mounts the same envelope and boots the same display widget as a server-rendered page. Details
and caveats in [Vue.js legacy interop](ui-js-vuejs-interop.md#richtextoutput). Note that
`onAfterOutput()`, `EVENT_AFTER_OUTPUT` and `EVENT_AFTER_RUN` do not fire on this path; a
module that appends markup there has to attach its data through the API's `SerializeEvent`
instead (see [Vue.js extensions](ui-js-vuejs-extensions.md)).

### Conversion: mails, notifications, search

Where no browser renders, `RichText::convert($text, $format, $options)` produces a finished
representation on the server:

| Format | Result |
|---|---|
| `FORMAT_HTML` | HTML. `RichTextToEmailHtmlConverter::process($text, [OPTION_RECEIVER_USER => $user])` is the mail variant with inline styles and links built for the receiving user. |
| `FORMAT_MARKDOWN` | Portable markdown: a mention becomes `[@Name](profile url)`, a file link a link to the file (an image stays an image), an oembed link a plain link. |
| `FORMAT_PLAINTEXT` | Text without markup, e.g. for the search index. |
| `FORMAT_SHORT_TEXT`, `FORMAT_SHORT_HTML` | Truncated single-line variants for notifications, activities and previews. `RichText::preview($text, $maxLength)` is the shorthand for `FORMAT_SHORT_HTML`. |

The converters in `converter/` build on `cebe/markdown`'s GitHub flavor and accept options
such as `exclude` (blocks or extensions to leave out), `linkTarget`, `linkAsText`,
`imageAsLink`, `imageAsText`, `maxLength`, `preserveNewlines`, `nl2br` and `cacheKey` (caches
the result; `exclude` is part of the key). Extensions take part through `onBeforeConvert()` and
`onAfterConvert()` — `RichTextEmojiExtension` turns shortcodes into UTF-8 here — and, for link
extensions, `onBeforeConvertLink(LinkParserBlock $block)`, which the converter calls for every
link whose scheme matches the extension's key so the extension can set the final URL, text or
title (`MentioningExtension` sets the profile URL, `FileExtension` the file URL,
`OembedExtension` strips its scheme).

## Extensions (PHP)

An extension implements `humhub\modules\content\widgets\richtext\extensions\RichTextExtension`.
Its hooks, in the order a text meets them:

| Hook | When | Purpose |
|---|---|---|
| `onPostProcess($text, $record, $attribute, &$result)` | `RichText::postProcess()` after save | Side effects of the saved text (attach, notify, preload); may normalize the text. |
| `onBeforeOutput($richtext, $output)` | `getMarkdown()`, i.e. `output()` **and** `outputMarkdown()` | Rewrite the markdown for display. Must not depend on the current user — the result is served to everyone. |
| `onAfterOutput($richtext, $output)` | `output()` only, after the envelope | Append markup next to the envelope, per-user work allowed. |
| `onBeforeConvert($text, $format, $options)`, `onAfterConvert(...)` | `convert()` | Prepare or post-process the text for a server-side format. |

Two base classes save most of the work. `RichTextContentExtension` is a `yii\base\Model` with a
regex contract (`getRegex()`, `initMatch()`) and the static helpers `scan()` and `replace()`
for anything that is not a link — the emoji extension is one. `RichTextLinkExtension` is the
base for a link scheme: it knows the `[text](key:id "title")` pattern, offers
`buildExtensionLink()`, `scanLinkExtension()`, `replaceLinkExtension()` and
`cutExtensionKeyFromUrl()`, and adds the `onBeforeConvertLink()` hook. The core extensions:

| Key | Class | Post-process | Before output | Convert |
|---|---|---|---|---|
| — | `RichTextCompatibilityExtension` | — | rewrites legacy syntax | rewrites legacy syntax |
| `mention` | `MentioningExtension` | creates `Mentioning` records | refreshes name and URL | link to the profile, `@Name` in markdown |
| `file-guid` | `FileExtension` | attaches files, stores base64 images | — | link or image with the file URL |
| `oembed` | `OembedExtension` | preloads previews | — (previews are appended in `onAfterOutput()`) | plain link |
| — | `RichTextEmojiExtension` | — | — | shortcode → UTF-8 |

A module registers its own with `ProsemirrorRichText::addExtension('my-key', MyExtension::class)`.
The registry is static and per request, so the call has to run before the first rich text is
rendered — an event handler for `yii\base\Application::EVENT_BEFORE_REQUEST` registered in the
module's `config.php` is the usual place. Two things to know:

- Extensions are singletons (`Model::instance()`), one per class for the whole request. State an
  extension keeps between `onBeforeOutput()` and `onAfterOutput()` is per render, nothing more.
- An extension defines how the platform **processes** a syntax. How it **renders** in the
  browser is a plugin's job. A new link scheme therefore needs both halves, and a scheme
  without a plugin renders as a dead link.

## Plugins (JavaScript)

The editor bundle is built from plugins, and a plugin serves both the editor and the display
renderer. A plugin is a plain object:

```js
const myPlugin = {
    id: 'my_plugin',
    // ProseMirror schema additions: nodes and marks with toDOM/parseDOM, plus how they
    // serialize to and parse from markdown.
    schema: { nodes: { /* … */ }, marks: { /* … */ } },
    // Runs once per editor or renderer instance; `isEdit` tells which.
    init(context, isEdit) { /* e.g. context.event.on('linkified', …) */ },
    // Editor side: ProseMirror plugins, keymap, menu items.
    plugins(context) { return []; },
    keymap(context) { return {}; },
    menu(context) { return []; },
    // Display side: markdown-it rules and renderer rules.
    registerMarkdownIt(markdownIt) { /* markdownIt.inline.ruler.before('link', 'my_plugin', …) */ },
    // Optional: restrict the plugin to one side.
    editorOnly: false,
    renderOnly: false,
};
```

Plugins are grouped in **presets**, and a widget names the preset it wants (`preset` on the PHP
widgets, default `full`). The core presets build on each other: `markdown` holds the formatting
plugins (paragraphs, headings, emphasis, code, links, images, files, lists, tables, upload,
history, source mode, …); `normal` adds `emoji`, `mention` and `oembed`; `full` equals `normal`;
`document` adds `anchor` for long documents. `include` and `exclude`
adjust a preset per widget, `pluginOptions` configures individual plugins (a plugin reads them
with `context.getPluginOption('my_plugin', 'key', default)`). Platform-wide defaults for the
mention provider, emoji, oembed and link validation come from the `ui.richtext.prosemirror`
module's config.

A module registers a plugin or a preset through the bundle's API. Do it in the module function
body — that runs when the script loads, before anything on the page is rendered — and make the
module's asset bundle depend on `humhub\modules\content\assets\ProseMirrorRichTextAsset` so the
editor bundle is loaded first:

```js
humhub.module('mymodule.richtext', function (module, require, $) {
    const richtext = require('ui.richtext'); // the editor bundle, also window.humhub.richtext

    // Into an existing preset, positioned relative to another plugin …
    richtext.plugin.registerPlugin(myPlugin, { preset: 'normal', before: 'ordered_list' });

    // … or as a preset of its own, selected with 'preset' => 'mymodule' on the PHP widget.
    richtext.plugin.registerPreset('mymodule', {
        extend: 'normal',
        callback: (addToPreset) => addToPreset('my_plugin', 'mymodule'),
    });
});
```

`registerPlugin(plugin)` without a preset makes the plugin known so a preset callback or a
widget's `include` can pick it up.

## Extension and plugin side by side

| Feature | Extension (PHP) | Plugin (JavaScript) |
|---|---|---|
| Mention | creates notifications after save, refreshes name/URL on output, resolves the link on convert | autocomplete in the editor (search via `mentioningUrl`), renders the link with the container's data |
| File | attaches files after save, resolves the URL on convert | upload, paste and drop in the editor; renders links, images, video and audio |
| Oembed | preloads after save, appends the previews on a server-rendered page, plain link on convert | turns a pasted link into an embed, renders the preview `humhub.oembed.js` knows |
| Emoji | UTF-8 on convert | picker in the editor, twemoji images on display |

## Events

- PHP: `AbstractRichText::EVENT_POST_PROCESS` (after `postProcess()`, with the result array),
  `EVENT_BEFORE_OUTPUT` and `EVENT_AFTER_OUTPUT` (`ParameterEvent`, parameter `output`, on
  `output()`; only the former fires on `outputMarkdown()`), `yii\base\Widget::EVENT_AFTER_RUN`.
  `UrlOembed::EVENT_FETCH` lets a module supply or override an oEmbed provider, see
  [OEmbed providers](advanced-oembed.md).
- JavaScript: `afterRender` (jQuery event on the rendered element; the content module's
  syntax highlighting listens to it), `humhub:content:afterSubmit` (resets the editor's draft
  backup).

## Settings

| Setting | Where | Effect |
|---|---|---|
| `params['richText']['class']` | `protected/config/common.php` | The rich text implementation; `RichText`, `RichTextField` and `RichText::convert()` dispatch to it. |
| `richtextCompatMode` | content module property and db setting | Legacy-syntax rewriting; both have to be on for it to run. |
| `oembed.requestConfirmation` | admin setting (default on) | Embedded previews need the user's consent per domain; a user's allowed domains are stored in `allowedOembedUrls`. Guests always see the preview. |
| `oembedProviders` | admin setting | The provider list, see [OEmbed providers](advanced-oembed.md). |
| `OembedExtension::$maxOembed` | static | Previews per text a server-rendered page embeds (10); the Vue side applies the same limit. |
