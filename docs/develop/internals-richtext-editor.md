# Richtext editor

The browser side of [rich text](concept-richtext.md): the ProseMirror-based editor, the markdown
renderer of the display widget, and every plugin. This page covers where the editor lives, how
it is built, tested and updated, and how an editor instance is configured. The format, the
pipelines and how a module writes a plugin are in [Rich text](concept-richtext.md).

Up to HumHub 1.19 the editor was the separate npm package `humhub-prosemirror-richtext`
([humhub/humhub-prosemirror](https://github.com/humhub/humhub-prosemirror)), where its history
and the maintenance line of older HumHub versions remain.

## Where it lives

| Path | What |
|---|---|
| `protected/humhub/resources/js/richtext/` | The sources. `index.js` is the entry: it exports the editor as `window.prosemirror` / `humhub.richtext` and as the `ui.richtext` module. `core/` holds the editor (`context.js`, `schema.js`, `menu/`, `plugins/<id>/`), `markdown/` the parser, serializer and renderer. |
| `protected/humhub/resources/js/humhub.richtext.editor.js` | The built artifact, committed. |
| `humhub\assets\ProsemirrorEditorAsset` | Publishes the artifact. A module depends on it, or on `content\assets\ProseMirrorRichTextAsset` (the widgets that boot it). |
| `protected/humhub/tests/js/richtext/` | The tests (vitest). |
| `richtext.build.mjs` | The build. |

The sources are only build input: the publish of `@humhub/resources` leaves `js/richtext/` out
(`AssetBundle::HUMHUB_RESOURCES_PUBLISH_OPTIONS`), and so do the release packages
(`.github/release/exclude.txt`).

## Building

The artifact is committed like the Vue artifacts (see [Vue.js](ui-js-vuejs.md)), so an
installation or a fresh checkout runs without a JavaScript build. It is not part of
`grunt build-assets`, which combines and compresses the published files (the artifact among
them) but builds nothing: whoever changes a source, or a dependency, rebuilds it and commits the
result.

```sh
npm run build:richtext            # one build
node richtext.build.mjs --watch   # rebuild on every save while developing
```

The build (Vite) bundles the sources and their dependencies into one self-executing script,
not minified - production compresses it with the rest - and byte-reproducible. jQuery and the
HumHub client are the page's globals. The CI (`js-test.yml`) rebuilds it and fails when the
committed artifact differs from its sources.

## Testing

```sh
npm run test:js                                   # every core JS test
npx vitest run protected/humhub/tests/js/richtext # the editor's
```

The tests boot the editor from its sources on a fixture page, standalone - without the HumHub
client, as on a page outside HumHub (`testEditor.js`, `standalone.js`) - and drive it through
`testEditor.js`'s helpers (`initEditor()`, `type()`, `clickMenuItem()`, `serialize()`, …).
jsdom lays nothing out: `standalone.js` supplies what it lacks (`:visible`, the rects of a
range, `DataTransfer`, `ClipboardEvent`), and a test that depends on heights stubs them (see
`max_height`).

## Dependencies

ProseMirror, markdown-it, CodeMirror (the source mode's code view) and the emoji picker
(`emoji-picker-element`) are `devDependencies` of the core's `package.json`, locked in
`package-lock.json` and installed into `node_modules/`. They are needed to build only; the
artifact contains them. The emoji font and data have a build of their own, see [Emojis](internals-emoji.md).

```sh
npm outdated                              # what is behind
npm install -D prosemirror-view@latest    # update one (or: npm update)
npm run build:richtext && npm run test:js
```

Commit `package.json`, `package-lock.json` and the artifact together. The CI builds with
`npm ci`, i.e. with exactly the locked versions, so an update committed without its rebuild
fails there.

## Configuration

An editor instance is configured by its options - from the PHP widget (`preset`, `include`,
`exclude`, `pluginOptions`, …, see [Rich text](concept-richtext.md)) or, in JavaScript, the
second argument of `new MarkdownEditor(selector, options)`. Platform-wide defaults go into
`window.prosemirror.globalOptions`.

### Presets

A preset is a predefined set of plugins (and, through them, of menu items):

- `markdown`: plain markdown.
- `normal`: extends `markdown` with emoji, mentions and oembed.
- `full` (default): extends `normal`; adds nothing itself, but modules may extend it.
- `document`: extends `full` for document-style texts such as wiki pages, adds `anchor`.

### Include or exclude plugins

- `include`: adds a plugin the preset does not have.
- `exclude`: removes a plugin of the preset.
- `only`: a fixed set of plugins (at least `doc`, `text` and `paragraph`).

```js
// The full preset without emoji
new MarkdownEditor('#stage1', { exclude: ['emoji'] });

// The markdown preset plus emoji
new MarkdownEditor('#stage2', { preset: 'markdown', include: ['emoji'] });

// Only these plugins
new MarkdownEditor('#stage3', { only: ['doc', 'text', 'paragraph', 'strong'] });
```

The renderer cannot leave out the core markdown plugins, so exclude only the platform's
additions (emoji, mentions, …) and hide other features from the menu instead (below). An instance
that includes or excludes plugins builds its own plugin set instead of the preset's cached
one, which costs time with many editors on a page.

### Register a plugin to a preset

```js
window.prosemirror.plugin.registerPlugin({
    id: 'test',
    menu: (context) => [{
        id: 'testItem',
        group: 'format',
        item: new window.prosemirror.menu.MenuItem({
            run: (state, dispatch) => dispatch(state.tr.insertText('test!')),
            label: 'test',
        }),
    }],
}, 'markdown');
```

Register before the first editor is created, or the presets extending that one miss the plugin.
How a module does it, and the plugin object itself: [Rich text](concept-richtext.md).

### Hide menu items

Usually safer than excluding a plugin: leave out menu items (or whole groups) by id, globally,
per preset or per instance.

```js
// Globally
window.prosemirror.globalOptions.menu = { exclude: ['insertTable'] };

// Per preset
window.prosemirror.globalOptions.presets = { wiki: { menu: { exclude: ['insertTable'] } } };

// Per instance
new MarkdownEditor('#stage1', { menu: { exclude: ['insertTable'] } });
```

### Translation

The editor translates its texts through `translate`, a map or a function, per instance or
globally. In HumHub the `ui.richtext.prosemirror` widgets pass the platform's translations;
only an editor booted without them needs its own:

```js
new MarkdownEditor('#stage1', { translate: { 'Test me!': 'Teste mich!' } });

window.prosemirror.globalOptions.translate = (key) => ({ 'Test me!': 'Teste mich!' })[key];
```

### Upload

By default the editor's upload (its "Upload File" menu entry, and files pasted into it) goes
through the legacy upload button the PHP widget renders next to the editor
(`#<editor id>-file-upload`). An editor without one - such as `RichTextField`, the editor of the
Vue.js forms - passes an `upload` option instead: a function receiving the pasted files, or
`null` from the menu entry (the host picks the files itself), and resolving with the uploaded
ones. Each is inserted where the selection was - an image, video or audio file as such,
anything else as a link. Resolving with none (the picker was cancelled) or rejecting just
removes the placeholder.

```js
new MarkdownEditor('#stage', {
    // Promise<[{guid, url, fileName|name, mimeType, mimeIcon}]>; `accept` narrows a picker (`image/*`)
    upload: (files, { accept }) => myUpload(files, accept),
    // The file handler links (`a[data-action-process=file-handler]`) the upload menu offers
    fileHandlers: () => document.querySelector('#my-handlers'),
});
```

The upload menu offers the file handlers (`fileHandlers`, by default the links next to the
legacy upload button). With the `upload` option the platform's "upload this type" entries
(`file.uploadByType`) go through it with that type; any other entry runs its own action, and
the file it announces on `humhub:file:created` is inserted into the editor whose menu ran it.

After an image is uploaded and inserted - by any of these ways - the editor asks for its
alternative text (for screen readers) in the platform's dialog, one per image: the core Vue
component `ImageAltTextModal`, mounted through `humhub.vue.mountElement()`. Skipping keeps the
file name; an empty text marks the image as decorative. Without the Vue runtime (outside
HumHub) the editor does not ask. `image: { altTextPrompt: false }` turns it off.
