# Emojis

Since HumHub 1.20 emojis are **Unicode text**: the editor writes the emoji character into the
markdown, and a font renders it. There are no emoji images, nodes or shortcodes any more, so
selecting, copying and searching behave like text, and the API, the mobile app, mails and the
search index get the emoji without any mapping.

## Rendering: a font

| Style | What renders the emojis |
|---|---|
| Noto Color Emoji (default) | The font family `'HumHub Emoji'` of `humhub\assets\EmojiFontAsset`, second in `$font-family-base` (`variables.scss`) after the text font. Every text of the platform gets it, without wrapper markup. |
| System | The asset is not registered, `'HumHub Emoji'` has no `@font-face`, and the font stack falls through to the emojis of the operating system. |

The administrator chooses the style under Administration → Appearance → Theme ("Emoji style",
core setting `emojiStyle`, `humhub\helpers\EmojiHelper`). `humhub\components\Appearance` registers
the font asset on every page of an installed platform.

The font is [Noto Color Emoji](https://github.com/googlefonts/noto-emoji) (2D, COLRv1, SIL Open
Font License, `fonts/emoji/OFL.txt`), cut into one WOFF2 file per emoji group (smileys, people,
flags, …). Each `@font-face` lists the code points of its group as `unicode-range`, so a browser
downloads only the files a page needs. Every emoji sequence (skin tones, ZWJ sequences, keycaps,
flags) lies completely in one file, so the browser shapes it with one font. The emojis are 15 % larger than the text around them (`size-adjust`), as the Twemoji images were. Digits, `#`, `*`,
`©` and `®` are part of the symbols file for the keycaps (`1️⃣`); plain text keeps the text font,
as it comes first in the font stack.

Browsers without COLRv1 support (Safari) skip the font (`tech(color-COLRv1)` in `src`) and show
their own emojis. Mails contain the emoji as text; the mail client renders it.

## Picker, autocomplete and shortcuts

The editor plugin `emoji` (`resources/js/richtext/core/plugins/emoji/`) has:

- **Picker:** the menu item "Insert Emoji" opens [emoji-picker-element](https://github.com/nolanlawson/emoji-picker-element),
  a web component with search, categories, skin tones, frequently used emojis, keyboard navigation
  and ARIA. Its texts are translated by the editor (`ContentModule.richtexteditor`), its colours
  come from the Bootstrap variables (`_richtext.scss`).
- **Autocomplete:** `:` and two letters at the start of a word (`:smi`) list matching emojis, by
  shortcode and by name in the user's language; Enter or Tab inserts the emoji, Escape closes the
  list.
- **Text shortcuts:** `:)`, `<3`, `;)` and about 25 more turn into an emoji while typing. Shortcuts
  ending with a letter (`:D`, `:P`) only once a space follows, so `:dog` still starts the
  autocomplete.
- **Paste:** an emoji image of pasted HTML (`<img class="emoji" alt="😀">`, e.g. copied from
  HumHub 1.19) becomes its `alt` text.

Picker and autocomplete read the data of the user's language, `resources/emoji/data/<locale>.json`
(names, keywords and shortcodes of [emojibase](https://emojibase.dev), MIT), published with
`@humhub/resources`. The JS config `ui.richtext.prosemirror.emoji` names it (`dataUrl`, `locale`,
see `EmojiHelper::getDataUrl()`). It is loaded when the picker or the autocomplete is used for the
first time and cached in the browser's IndexedDB, nothing of it is part of the editor bundle.
With the Noto font, the picker shows every emoji of the font (`emojiVersion`) instead of testing
which ones the operating system can render.

A module opens the same picker with `humhub.richtext.openEmojiPicker(anchor, {onSelect: (emoji) => …})`
(the editor bundle, `ProsemirrorEditorAsset`), or uses the `<emoji-picker>` element it registers.

## Texts saved before 1.20

Older texts store emojis as shortcodes: `:grinning face:` (the picker), `:smile:` or `:+1:`
(legacy names) and `;smile;` (the editor before 1.3). `RichTextEmojiExtension` converts them:

- on output (`onBeforeOutput()`), which covers the display, the editor's initial markdown and
  `RichText::outputMarkdown()` of the API,
- on conversion to HTML, mail, plain text and short text (`onBeforeConvert()`),
- on save (`onPostProcess()`), so `RichText::postProcess()` writes the text back as Unicode
  whenever it is edited.

Only names of the frozen map `extensions/emoji/legacy-shortcodes.php` are converted, never inside
code spans or fenced code blocks. The map was generated once from the emoji libraries up to 1.19
and never changes, as new texts contain no shortcodes. There is no bulk migration.

## Building

`emoji.build.mjs` (`npm run build:emoji`) builds the committed artifacts:

| Output | From |
|---|---|
| `protected/humhub/resources/fonts/emoji/` — the WOFF2 files, `emoji.css`, `OFL.txt` | The Noto release pinned in the script (`NOTO_RELEASE`, checked by `NOTO_SHA256`), downloaded once into `node_modules/.cache/humhub-emoji/`, cut with fontTools (`@web-alchemy/fonttools`) along the groups of `emojibase-data` |
| `protected/humhub/resources/emoji/data/` — the picker data per language | `emojibase-data` |

The output is byte-reproducible. To support a new Unicode emoji version, update `emojibase-data`
and the Noto release (`NOTO_RELEASE`, `NOTO_SHA256`), raise `NOTO_EMOJI_VERSION` in
`plugins/emoji/data.js`, then rebuild the emoji assets and the editor:

```sh
npm install -D emojibase-data@latest
npm run build:emoji && npm run build:richtext && npm run test:js
```
