# Vue.js Form Suite

> Part of the [Vue.js integration](ui-js-vuejs.md) documentation. This chapter covers the native `HumHubForm` component suite: `HumHubForm` itself, the field components (`TextField`, `TextareaField`, `CheckboxField`, `SelectField`, `PickerField`, `UploadField`), `SubmitButton`, the Yii-parity markup/`name`/`id` convention, the error contract, and the rich text editor as a field (`RichTextField`). For motivation, goals, constraints and the overall architecture, see the [overview](ui-js-vuejs.md).

## Why a form suite

A Vue island form needs Yii's `name`/`id`/error-class conventions, one error state and one busy state. Re-deriving them by hand in every module invites drift, so the suite provides **native field components** for standard inputs — pure Vue, markup-parity with `yii\bootstrap5\ActiveField`, sharing one error/busy context — and the platform's rich text editor as one of them (`RichTextField`).

**Validation stays server-side.** Yii model rules are the single source of truth for whether a value is valid; the client never re-implements them. `required`, `type`, etc. on a field are display/UX hints only (a visual marker, an `<input type>`) — the actual accept/reject decision, and the messages shown, always come from the server's response to a real submit attempt.

## Component reference

All components below are top-level in `protected/humhub/vue/` and therefore auto-registered platform-wide (see [Components: module file layout](ui-js-vuejs-components.md#module-file-layout)) — no import needed to use them from any module's island template.

| Component | Purpose | Key props | Emits / exposes |
|---|---|---|---|
| `HumHubForm` | Form root: renders `<form>`, provides context | `modelName` (String), `busy` (Boolean) | emits `submit`; exposes `setErrors()`, `clearErrors()`, `focusFirstError()`, `focusFirstField()` |
| `TextField` | Single-line input | `attribute`*, `label`, `hint`, `placeholder`, `required`, `disabled`, `modelValue`, `type` (`text`/`email`/`password`/`number`, default `text`) | `update:modelValue` |
| `TextareaField` | Multi-line input | same as `TextField` + `rows` (default 4) | `update:modelValue` |
| `CheckboxField` | Checkbox | `attribute`*, `label`, `hint`, `required`, `disabled`, `modelValue` (Boolean) | `update:modelValue` |
| `SelectField` | `<select>` dropdown | `attribute`*, `label`, `hint`, `required`, `disabled`, `modelValue`, `options` (`[{value, label}]`), `prompt` | `update:modelValue` |
| `PickerField` | Picker / enhanced select, see [Picker](ui-js-vuejs-picker.md) | `attribute`*, `label`, `hint`, `placeholder`, `required`, `disabled`, `modelValue`; every `PickerInput` prop (`multiple`, `source`, `url`, `options`, `initialItems`, `maxSelection`, `listed`, …) | `update:modelValue` |
| `SubmitButton` | `type="submit"` button | `disabled`, `loader` (default `true`) | default slot = label/icon |
| `UploadField` | File uploads, see [File uploads](#file-uploads) | `attribute`*, `modelValue` (file shapes), `max`, `accept`, `multiple`, `title`, `handlersHtml`, `triggerTarget` | `update:modelValue`, `busy`; exposes `addFiles()`, `openPicker()`, `clear()` |
| `RichTextField` | The richtext editor, see [Rich text](#rich-text) | `attribute`*, `instanceKey`, `mentioningUrl`, `pluginOptions`, `exclude` | exposes `getValue()`, `setValue()`, `clear()`, `focus()` |

\* `attribute` is required on every field — it is both the Yii model attribute this field maps to and the key its server-side errors are read from.

Every field shares its error/busy/naming behavior through an internal mixin (`protected/humhub/vue/form/fieldMixin.js`) injecting `HumHubForm`'s context — see [`form/` internals](#internals) if you are extending the suite itself, not just using it.

## The `modelName`/`name`/`id` convention

`<HumHubForm model-name="Comment">` makes every nested field's `name` attribute `Comment[<attribute>]` and its `id` the lowercase, dash-joined equivalent (`comment-message`) — exactly `Html::getInputName()`/`Html::getInputIdByName()`'s own convention, so a native field and a server-rendered `ActiveForm` field for the same model/attribute are indistinguishable to a `label[for]`, a browser extension, or a test selector. A field with no ancestor `HumHubForm`, or a `HumHubForm` with no `modelName`, falls back to the bare `attribute` for both.

Markup otherwise mirrors `yii\bootstrap5\ActiveField`'s own defaults byte-for-byte where it matters visually — see each field component's own docblock for the exact reference (file:line into `protected/vendor/yiisoft/yii2-bootstrap5/src/ActiveField.php` / `protected/vendor/yiisoft/yii2/widgets/ActiveField.php`) it was checked against: the `mb-3 field-<id>[ required]` container, `form-label`/`form-control`/`form-select`/`form-check-input`, `is-invalid`, `invalid-feedback`, `form-text text-muted`, and — for checkboxes specifically — the different `{input}{label}{error}{hint}` part order `ActiveField::$checkTemplate` uses (error before hint, unlike every other field's `{label}{input}{hint}{error}`).

One deliberate deviation from `ActiveField`: a real `ActiveField`'s error part only ever shows the attribute's FIRST message (`Html::error()` → `getFirstError()`). Every field in this suite renders EVERY message for its attribute instead, since the 422 payload it reads from can legitimately carry more than one failed rule and a live client round-trip has that information available.

## The error contract

A validation failure of the HTTP API answers `422 {"errors": {"<field>": [message, ...]}}`, one message array per camelCased field (see [HTTP API framework](concept-api.md)); a legacy controller may answer the bare `{attribute: [message, ...]}` of `Model::getErrors()`. Call `setErrors()` on the `HumHubForm` ref with the raw response — it accepts every shape below, so callers never need to know which one they got:

```js
formRef.setErrors(response); // any of the three shapes below
```

| Shape | When |
|---|---|
| `{attribute: [messages]}` | The raw Yii validation-error body itself |
| `{errors: {attribute: [messages]}}` | A `client.Response`-flattened 422 (`status`, `errors`, ... at the top level) |
| `{error: {errors: {attribute: [messages]}}}` | A response whose `Content-Type` wasn't sniffed as JSON, so the body only surfaced via a fallback `.error` property |

Each field watches its own `attribute` key in the resulting map and renders whatever is there; typing into a field with an error clears just that field's entry (`clearError(attribute)`, wired into every field's own input handler) — the rest of the map, and any other field's error, is untouched. Call `clearErrors()` before a fresh submit attempt (so a stale error from a previous failed attempt never lingers past a request that might succeed), and `focusFirstError()` after `setErrors()` to move focus to the first-registered (template order, not object-key order) field that actually has one.

`HumHubForm` never performs the HTTP request itself — see its own docblock for why (endpoint/payload shapes vary too much module to module to usefully centralize) — the consumer's `@submit` handler (or a button's `@click`, see [`SubmitButton`](#submitbutton-and-native-enter-to-submit) below) builds and posts the payload, then calls `setErrors()`/`clearErrors()` itself.

## Worked example

A hypothetical module form editing a `Widget` model's `title` (text) and `kind` (select), posting to its own controller action:

```html
<template>
    <HumHubForm ref="form" model-name="Widget" :busy="busy" @submit="onSubmit">
        <TextField attribute="title" v-model="title" label="Title" required />
        <SelectField
            attribute="kind"
            v-model="kind"
            label="Kind"
            :options="[{ value: 'a', label: 'Kind A' }, { value: 'b', label: 'Kind B' }]"
            prompt="Please select"
        />
        <SubmitButton class="btn btn-primary">Save</SubmitButton>
    </HumHubForm>
</template>

<script>
import { client, url } from '@humhub/vue';

export default {
    data() {
        return { title: '', kind: '', busy: false };
    },
    methods: {
        onSubmit() {
            if (this.busy) {
                return;
            }
            this.busy = true;
            this.$refs.form.clearErrors();

            client.post(url('/my-module/widget/create'), {
                data: { title: this.title, kind: this.kind },
            }).then(() => {
                this.busy = false;
                // ... e.g. emit an event, close a modal, redirect
            }).catch((response) => {
                this.busy = false;
                if (response && response.status === 422) {
                    this.$refs.form.setErrors(response);
                    this.$refs.form.focusFirstError();
                } else {
                    // fall back to the generic log/status path, same as every
                    // other island's non-validation error handling
                }
            });
        },
    },
};
</script>
```

Note `SubmitButton` carries no default class of its own (see its own docblock) — the caller supplies the button's full visual style (`class="btn btn-primary"` above), and `type="submit"` + busy-disabling + the busy-loader swap come for free.

### `SubmitButton` and native Enter-to-submit

Because `HumHubForm` renders a real `<form @submit.prevent>`, pressing Enter inside a `TextField` (a real `<input>`) triggers native form submission the browser already knows how to do — which `HumHubForm` intercepts and turns into the same `submit` emission a `SubmitButton` click reaches via `@submit.prevent`. A module using only native fields therefore gets Enter-to-submit for free without wiring anything for it.

## File uploads

`UploadField` is the native counterpart of the legacy `file\widgets\Upload*` composition
(`UploadButton` + `UploadProgress` + `FilePreview`, driven by `humhub.file.js`):

```html
<UploadField
    ref="upload"
    attribute="fileList"
    v-model="files"
    :max="10"
    :handlers="handlers"
    :trigger-target="buttonRow"
    @busy="uploadBusy = $event"
/>
```

**The field owns the guid list, the form submits it.** Files are uploaded to
`POST /api/v2/file` (see [HTTP API framework](concept-api.md)) as soon as they are added, and
`modelValue` holds the API's own file shapes (`{id, guid, fileName, mimeType, size, mimeIcon,
url, previewUrl}`). Nothing is attached server-side while the user edits — the surrounding form
sends the guids with its own request (`fileList` for a comment). Two consequences worth knowing:

- Removing an entry before submitting means the file is never attached; it stays behind
  unattached and the file module's cron job cleans it up.
- In an EDIT form the field starts empty and only newly added files are submitted, because
  `attach()` on the model side never detaches — a record's existing attachments survive an
  edit untouched.

**Batch semantics.** One request carries the whole selection, and the response reports per
file (`{results, errors}`), so one rejected file does not discard the accepted ones — the field
renders each rejected file's messages and keeps the rest. A non-validation failure goes to the
platform's error surface (the status bar) like everywhere else in the islands.

**Placement.** Progress and the file list render where the field sits; the trigger button group
can be Teleported elsewhere via `triggerTarget` — the comment form puts it in the same
`.richtext-create-buttons` row as its submit button. Drop and paste work on the field's own
root; a caller with a larger drop zone (the comment form: the whole comment box) forwards those
events to `addFiles()`.

**File handlers.** The import and create handlers of the current user come with every page
as data, in the core JS config (`getConfig('file').handlers`: `{label, icon, attributes}`, see
`file\serializers\FileHandlerSerializer`). Pass them as `handlers` and the field renders them in
its own dropdown as links carrying their attributes, so their `data-action-click` keeps being
served by `humhub.action.js`'s document-level delegate; a file a module's handler creates
(`humhub:file:created`) is attached to the field whose entry was clicked. Core's own "upload with this `accept` type" entries (`file.uploadByType`) are handled
natively by the field. A handler that produces an already-uploaded file hands it over with a
DOM event instead of reaching into a widget instance:

```js
element.dispatchEvent(new CustomEvent('humhub:file:attach', { detail: { files: [fileShape] } }));
```

## Rich text

`RichTextField` holds the platform's rich text editor (ProseMirror, see [Richtext editor](internals-richtext-editor.md)), rendered client-side — no server-rendered form shell. It renders the editor's mount element with the options the PHP widget (`content\widgets\richtext\RichTextField`) would put on it as data attributes and boots the same `ui.richtext.prosemirror.RichTextEditor` widget on it through `v-additions`, so plugins and presets a module registers on the editor (`prosemirror.plugin.registerPlugin()`/`registerPreset()`) apply here as in any PHP form. The PHP widget stays the way to put the editor into a server-rendered form.

```html
<RichTextField
    attribute="message"
    layout="inline"
    :instance-key="'c' + contentId"
    :placeholder="placeholder"
    :mentioning-url="mentioningUrl"
    :plugin-options="{ maxHeight: '300px' }"
    upload
    :handlers="handlers"
/>
```

- Options as on the PHP widget: `preset`, `include`, `exclude`, `pluginOptions`, `mentioningUrl`, `placeholder`; `layout` `inline` (menu on focus) or `block` (always shown, the default); `autofocus`.
- `instanceKey` keys the editor's draft backup (`sessionStorage`): unique among the fields mounted at once and stable across page loads for the same logical form. Without one the field keeps no backup.
- API: `getValue()`, `setValue(markdown)`, `clear()` (also drops the draft), `focus()`, `getElement()`. Events: `scroll-active`/`scroll-inactive` for a height-limited editor.
- `upload` puts files into the text: `true` uploads through `POST /api/v2/file`, a function `(files) => Promise<file[]>` uploads itself. The editor's menu entry then opens a file picker, a pasted file is uploaded too, and each file is inserted at the cursor (the server attaches what the text references on save). Without it the editor's upload is left out. Attachments are an `UploadField`'s job (see [File uploads](#file-uploads)).
- `handlers` (the file handlers, as `UploadField` takes them) adds the file handlers to the editor's upload menu: the platform's "upload this type" entries go through `upload` with that type, a module's entry runs its own action and the editor inserts the file it creates. Without them the editor's file handler plugin is left out.
- Its `attribute`'s server-side messages render under the editor, like a `TextField`'s. No `label`/`hint` is rendered, and `disabled`/busy do not reach the editor; `SubmitButton`'s busy-disable guards a request in flight.

## Reference: `CommentForm.vue`

`comment/vue/components/CommentForm.vue` is the suite's reference consumer: `<HumHubForm model-name="Comment">` with a `RichTextField` (`attribute="message"`), an `UploadField` whose trigger is teleported into the form's button row, and a `SubmitButton` beside it. The editor's Ctrl+S clicks the `[type="submit"]` of the editor's form, i.e. that button. See its own docblock for the details.

## Internals

`protected/humhub/vue/form/` (a subdirectory of `vue/`, therefore never auto-registered — see [module file layout](ui-js-vuejs-components.md#module-file-layout)) holds the suite's own building blocks:

- `formContext.js` — the `provide`/`inject` key, and `toInputId()` (mirrors `Html::getInputIdByName()`).
- `fieldMixin.js` — the shared Options API mixin every field (including `RichTextField`) uses for name/id resolution, error/busy state and form registration/focus.

Building a new field component: mix in `fieldMixin`, declare your own `modelValue`/type-specific props and `emits: ['update:modelValue']`, and implement a `focus()` method targeting your field's own interactive element (used by `HumHubForm.focusFirstError()`).
