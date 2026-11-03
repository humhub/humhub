# Vue.js Picker

> Part of the [Vue.js integration](ui-js-vuejs.md) documentation. This chapter specifies `PickerInput`, the one picker and enhanced select of the platform. It replaces Select2 everywhere: the `BasePicker` widget family, the `data-ui-select2` selects of `Html::dropDownList()` and the `IconPicker`. The look and the behaviour follow [Select & Pickers](../ui/select-pickers.md).

> **Status: implemented since 1.20.**

## Goals

- **One implementation** for every searchable or enhanced choice: PHP forms, Vue forms and filter bars share one combobox.
- **Works in every form.** A picker in a server-rendered `ActiveForm` posts its value with the form, validates with `yii.activeForm` and reacts to `form.reset()`, exactly as a native `<select>` does. Vue is only the UI on top.
- **The PHP API stays.** `BasePicker` and its subclasses keep their properties and abstract methods; modules that render a picker or subclass one keep working.
- **No server HTML in the client.** Item images are descriptions rendered by Vue components, never HTML strings.
- **Select2 leaves the platform**: its assets, its SCSS, the Select2 based `humhub.ui.picker.js` and every use of `kartik\select2` (the package stays installed only as dependency of `kartik-v/yii2-widgets`).

Out of scope: date, time and duration pickers.

## Architecture

```
PHP (BasePicker, Html::dropDownList)
  └─ renders <select name="Model[attr][]" data-ui-picker …> with <option selected …>   ← the value lives here
UI addition "picker" (humhub.ui.picker.js, rewritten)
  └─ hides the <select>, inserts <div data-vue-component="PickerInput"> after it, mounts it
PickerInput.vue
  ├─ reads the initial state from the <select>, writes every change back, fires "change"
  ├─ comboboxMixin            shared with FilterPicker / PickerFilterControl
  ├─ picker source            static | legacy | named (registerPickerSource)
  └─ PickerImage              by image type (registerPickerImage)
```

In a Vue form, `PickerField` (the form-suite field around `PickerInput`) or `PickerInput` itself is used with `v-model`; there is no `<select>` then.

## Markup contract

`BasePicker` and `Html::getDropDownListOptions()` render a native `<select>`; it is the only carrier of the value.

```html
<select id="task-assigned_user_guids" name="Task[assignedUserGuids][]" multiple class="form-control"
        data-ui-picker
        data-picker-source="user"
        data-picker-max-selection="50"
        data-picker-min-input="3"
        data-picker-placeholder="Add people"
        data-picker-config='{"lockedIds":["c9c0…"],"messages":{"noResults":"Your search returned no matches."}}'>
    <option value="c9c0…" selected
            data-text="Anna Admin"
            data-sub-text="Project Manager"
            data-image='{"type":"user","imageUrl":"/uploads/profile_image/c9c0….jpg"}'>Anna Admin</option>
</select>
```

### The `<select>`

- `data-ui-picker` marks the element. `data-ui-select2` is read as a deprecated alias until 1.21, so selects rendered by modules with the old attribute keep their enhancement; it takes the same `data-picker-*` attributes (the old `data-minimum-results-for-search`, `data-ui-select2-allow-new` and `data-ui-select2-new-sign` are read as `data-picker-search`, `data-picker-allow-new` and ignored respectively).
- **Scalar settings** are single `data-picker-<name>` attributes (kebab-case of the `PickerInput` prop, see [Props](#props)), so a view overrides one of them through `$options` alone. A boolean is set by its presence (`data-picker-allow-new`) or by `"true"`/`"false"`.
- **Lists and objects** travel in one JSON attribute, `data-picker-config` (`lockedIds`, `disabledIds`, `defaultResults`, `sourceParams`, `messages`). A single attribute wins over the same key in `data-picker-config` — the convention of the [Vue mounter](ui-js-vuejs-components.md#mounting-and-lifecycle).
- `multiple` on the `<select>` makes the value an array (`BasePicker` always renders `multiple`, as today). Chips show when `multiple` and `maxSelection` is not `1`; with `maxSelection = 1` the picker shows as a single record, the value staying an array of one, as today with Select2.
- `disabled` on the `<select>` disables the picker. `autofocus` on the `<select>` moves to the picker's input (so `ui.modal` focuses it).

### The `<option>`

| Attribute | Meaning |
|---|---|
| `value` | The posted value (`itemKey`: a guid for containers, an id otherwise) |
| `selected` | Chosen |
| text content / `data-text` | Label (`data-text` wins) |
| `data-sub-text` | Second line in the result list |
| `data-image` | Image: a string or an image description as JSON (see [Images](#images)) |
| `data-color` | Shorthand for `data-image='{"type":"color","color":…}'` (existing `Html::dropDownList` usage) |
| `disabled` | Shown but not selectable |
| `data-locked` | Chosen and not removable (rendered from `lockedIds` by `BasePicker`) |

`<optgroup label="…">` is supported: its label is a non-selectable header in the list.

For a **picker** (`data-picker-source` set) the options are the chosen items only. For an **enhanced select** (no source) the options are all choices, the selected ones chosen.

## Items

Every source delivers the same internal item:

```js
{ id: 'c9c0…', text: 'Anna Admin', subText: 'Project Manager', image: { type: 'user', … }, disabled: false, locked: false }
```

`id` is a string and is the posted value — read from the field named by `itemKey` (`guid` for containers; legacy endpoints such as the space search answer `guid` without `id`), else `id`. `subText`, `image`, `disabled`, `locked` are optional.

### Images

An image is a description, never HTML:

| `type` | Fields | Rendered by |
|---|---|---|
| `user` | `imageUrl`, `displayName` | `UserImage` (user module) |
| `space` | `imageUrl` (may be `null`), `name`, `color` | `SpaceImage` (space module): the image, or the acronym on the space colour |
| `icon` | `name` (Tabler name without prefix, e.g. `users-group`) | `<i class="ti ti-…">` |
| `color` | `color` | A colour dot (`.picker-color`) |
| `url` | `url` | `<img>` |

- `user` and `space` are registered by their modules in their Vue bundles (`UserVueAsset`, `SpaceVueAsset`), as `registerFilterType()` is: `registerPickerImage(type, componentName, toProps)` takes a registered component's name and a function `(image, size) => props` mapping the description to that component's props (`UserImage` takes `size`, `SpaceImage` `width`; both with `link: false`). `size` is `18` in a chip, `24` in a listed row, `32` in a result row (`40` for the whole list once a result has a sub text). A module may register its own type.
- `icon`, `color` and `url` are rendered by `PickerImage` itself.
- **A string** is read as today's Select2 picker read it: starting with `/` or `http` → `url`; `#…` → `color`; `ti-…` or `fa-…` → `icon` (a `fa-` name rendered as `fa fa-…`, as today, by the Font Awesome compatibility layer); any other word → `icon` with that Tabler name.
- **An HTML string** (containing `<`) renders no image; in debug mode a warning is logged. Endpoints that answer HTML today are migrated (core: see [Server endpoints](#server-endpoints); modules: [Module migration](#module-migration)).
- An unregistered `type` renders no image.

## Sources

A source is `{ search(term) → Promise<item[]>, newImage? }`. `newImage`, an image description, is what an item added with `allowNew` shows once chosen (the `topic` source gives the module icon, `params.icon`, default `star`); without it a new item has no image. In the list, the new item shows the add icon.

| Source | Selected by | Request | Answer |
|---|---|---|---|
| **static** | no `data-picker-source`/`data-picker-url` | — (the `<option>`s, filtered in the browser by `text`, case- and accent-insensitive) | — |
| **legacy** | `data-picker-url` | `GET <url>` with `keyword=<term>` (appended with `&` when the URL has a query) | `[{ id, text, subText?, image?, disabled? }]`, today's Select2 shape; `title`/`displayName` are read when `text` is missing, `displayNameSub` when `subText` is |
| **named** | `data-picker-source="<name>"` | what the registered source does | items |

- `registerPickerSource(name, factory)`: `factory({ itemKey, params })` returns a source; `params` are `sourceParams` of the config. The core modules register:
  - `user` (user module): `GET /api/v2/user/picker?q=…` plus `spaceId` from `params`; item `id` = the user's `guid` or `id` by `itemKey`, `text` = `displayName`, `subText` = `displayNameSub`, image `user`.
  - `space` (space module): `GET /api/v2/space?purpose=picker&q=…` plus `scope` from `params` (default `member`); `id` by `itemKey` (`guid` by default), `text` = `name`, `subText` = `description`, image `space`.
  - `icon` (core, registered by `ui.picker` in `humhub.ui.picker.js`): the names of `params.lib` (default `ti`) from the `ui.picker` config `iconNames.<lib>` (registered by `IconPicker`, read on every search) containing the term, case-insensitive; `id` = `text` = the name, image `icon` for Tabler, none for another library.
  - `topic` (topic module): `GET /api/v2/topic/picker?q=…` plus `containerId` from `params`; `id` = the topic id, `text` = `name`, image `color` when the topic has a colour, else `icon` with the topic module's icon.
- An unregistered source name falls back to static, with a debug warning.
- **Suggestions before typing** (remote sources — a static source always lists its options, filtered by the typed text): when the list opens with nothing typed, the picker shows
  - `defaultResults` from the config when the server rendered some (e.g. `TopicPicker`'s `showDefaults`, as today), else
  - with `suggested` > 0 the first `suggested` items of `search('')`, with `minInput` 0 the first `maxResults` items of `search('')`, else nothing (the list says `inputTooShort`);
  - `search('')` answers only when the source can answer an empty term; the core sources can when their context parameter is set (`user` with `spaceId`, `topic` with `containerId`, `space` always — the spaces of its scope). Without it, they answer nothing.
- `search` is called after `minInput` characters, debounced (`SEARCH_DEBOUNCE_MS`, 250 ms); an answer to an older term is dropped, answers are cached per term while the picker lives. At most `maxResults` items are shown (default 8 for a remote source, no limit for a static one).
- A failing request shows `messages.error` in the list and logs the error; the chosen items are untouched, the field stays usable.

## `PickerInput`

`protected/humhub/vue/PickerInput.vue`, a global core component (`<picker-input>`), styled by `_picker.scss`.

### Props

| Prop | Type | Default | Meaning |
|---|---|---|---|
| `modelValue` | `String` \| `Array` | `''` / `[]` | The chosen value(s) (`v-model`) |
| `multiple` | `Boolean` | `false` | Several items |
| `maxSelection` | `Number` | `0` | Most items held, `0` = no limit |
| `source` | `String` | `null` | Named source |
| `url` | `String` | `null` | Legacy source |
| `sourceParams` | `Object` | `{}` | Parameters of the named source |
| `itemKey` | `String` | `'id'` | `id` or `guid`: which field a named source posts |
| `options` | `Array` | `[]` | Static items (Vue usage; in native mode the `<option>`s) |
| `initialItems` | `Array` | `[]` | Items of the initial `modelValue`, so chosen values show their label (Vue usage; in native mode the selected `<option>`s) |
| `defaultResults` | `Array` | `[]` | Suggestions of a legacy source |
| `suggested` | `Number` | `0` | Suggestions before typing |
| `minInput` | `Number` | `0` | Characters before a search |
| `maxInput` | `Number` | `0` | Characters the input takes, `0` = no limit |
| `maxResults` | `Number` | `null` | Items listed per search; `null`: 8 for a remote source, all for a static one |
| `search` | `Number` | `5` | Enhanced select only: the search input appears from this many options on; `-1` never, `0` always |
| `allowNew` | `Boolean` | `false` | Offer the typed text as a new item |
| `newPrefix` | `String` | `''` | Prefix of a new item's value (`data-picker-new-prefix`) |
| `allowClear` | `Boolean` | `true` | Enhanced select: a × clears the value (the bridge sets it only when the `<select>` has an empty option) |
| `listed` | `Boolean` | `false` | Show the chosen items as rows below the field instead of chips |
| `lockedIds`, `disabledIds` | `Array` | `[]` | Not removable / not selectable |
| `placeholder`, `placeholderMore` | `String` | — | Input placeholder; with items chosen, `placeholderMore`; an enhanced select's empty option (`prompt`) is its placeholder |
| `label` | `String` | the placeholder | Accessible name (`aria-label` of the input; the bridge reads it from the `<label for>` of the `<select>`, whose click focuses the input) |
| `describedBy` | `String` | `null` | `aria-describedby` of the input (`PickerField` passes its hint and error ids) |
| `disabled`, `required`, `invalid` | `Boolean` | `false` | States (`invalid` = `is-invalid`) |
| `autofocus` | `Boolean` | `false` | Focus the input when mounted (`BasePicker::$focus`, `autofocus` on the `<select>`) |
| `messages` | `Object` | translated defaults | `noResults`, `inputTooShort` (`{n}`), `maximumSelected` (`{n}`), `loading`, `error`, `locked`, `add` (`{text}`), `remove` (`{text}`) |
| `id` | `String` | generated | The input's id |
| `name` | `String` | `null` | Vue usage: renders hidden inputs `<name>` / `<name>[]` |
| `selectId` | `String` | `null` | Native mode: the `<select>` it binds to |

Emits `update:modelValue`. In native mode the `<select>` is the model (see [Native bridge](#native-bridge)).

### Behaviour

- **Multiple** (`multiple`, `maxSelection` not 1): chosen items are chips in the field (image, label, a remove button "Remove <label>"), the input after them; Backspace in the empty input removes the last removable chip. With `listed`, the field holds only the input and the chosen items are rows below it (image, label, sub text, remove button), in the order chosen.
- **Single record** (a source, or a static `multiple` picker, with `maxSelection = 1` or not `multiple`): the chosen item shows in the field with its image; typing searches; the × clears it.
- **Enhanced select** (no source, not `multiple`): a closed field with the chosen label (and image) and a chevron; the list holds all options, the chosen one checked. From `search` options on, typing into the field searches (the field is the search input, as in the other modes); below that the field is read-only and typing a letter moves to the next matching option. A × clears only when the select has an empty option (a `prompt`).
- **Chosen items are not offered again** in multiple mode.
- **Matches are highlighted** in the result labels (`<mark>`, text-only, no HTML from the source).
- **`maxSelection` reached**: the list shows `messages.maximumSelected` instead of results.
- **Locked items** have no remove button and carry `messages.locked` as their title; they are sorted first (as `BasePicker::sortLockedItemsFirst()` does today).
- **Disabled items** are listed dimmed, not selectable, with `aria-disabled`.
- **`allowNew`**: while the typed text (at least `minInput` characters) matches no item's `text`, the list ends with `messages.add` ("Add: '…'"); the first match is active right after typing, the new item when nothing matches. The new item's value is `newPrefix` followed by the text. `BasePicker` renders `data-picker-new-prefix="_add:"` — the server contract it documents and `Topic::attach()` reads; `Html::dropDownList` sets none, so an enhanced select posts the plain text, also when `multiple`.
- **Feedback rows** (`.select-menu-feedback`): `inputTooShort`, `loading` (with spinner), `noResults`, `error`, `maximumSelected`; a visually hidden `role="status"` region announces them.
- **Keyboard and ARIA**: the combobox pattern of `FilterPicker` — input `role="combobox"` with `aria-expanded`, `aria-controls`, `aria-activedescendant`; list `role="listbox"`, options `role="option"` with `aria-selected`/`aria-disabled`; ArrowDown/ArrowUp open and move, Home/End, Enter chooses (on a closed list it opens it — Enter never submits the form, as on a `<select>`; on the new item while the answer to the typed text is still out, it chooses once the answer is in: an item of that name, else the new one), Escape closes (pressed again, drops the typed text), Tab closes and moves on; a click outside closes.
- **The list** (`.dropdown-menu.select-menu-drawer`) stays in the picker's DOM and is positioned `fixed` under the field, at the field's width, so an `overflow` of a dialog or a card never clips it; it opens above (`is-drop-up`) when there is less room below than its height and more above, its height limited to the visible area (`visualViewport`, so the on-screen keyboard counts). It follows the field on scroll and resize while open.

### Vue usage

```vue
<HumHubForm model-name="Task">
    <PickerField attribute="assignedUserGuids" multiple source="user" item-key="guid"
                 v-model="assignees" :initial-items="assigneeItems" :max-selection="10" />
</HumHubForm>
```

`PickerField` (`protected/humhub/vue/PickerField.vue`, `<picker-field>`) is the suite field (the field mixin) around a `PickerInput`: `attribute`, `label`, `hint`, the `name`/`id` convention, errors, busy; every other attribute is passed through to the `PickerInput` (`v-bind="$attrs"`). It is listed in the [Form suite](ui-js-vuejs-forms.md) component reference.

## Native bridge

`humhub.ui.picker.js` is rewritten as the UI addition `picker` on `select[data-ui-picker], select[data-ui-select2]`:

1. Skips a `<select>` it already bound (marker `data-picker-bound`); gives it an id when it has none.
2. Inserts `<div data-vue-component="PickerInput" data-props='{"selectId":"…"}'>` right after the `<select>` (before any `.invalid-feedback`, so Bootstrap's `.is-invalid ~ .invalid-feedback` still applies) and mounts it through `humhub.vue.mountElement()`.
3. Only once the island is mounted, hides the `<select>` visually (`.visually-hidden`, `tabindex="-1"`, `aria-hidden`; its `autofocus` moves to the picker's input). It is not `hidden`, so scripts and WebDriver's `selectOption()` still reach it, as with Select2. A failed mount leaves the native `<select>` visible and working.

`PickerInput` in native mode:

- **Reads** its props from the `<select>`'s `data-picker-*`/`data-picker-config` and its items from the `<option>`s.
- **Writes** each change to the `<select>`: a chosen item gets an `<option selected>` (created with `value`, `data-text`, `data-sub-text`, `data-image` when it is new, in the order chosen), a removed item's option is unselected (pickers remove it), then it dispatches a bubbling `change` on the `<select>`. `yii.activeForm` validation, the unsaved-changes check and module code listen to that.
- **Follows** the `<select>`: a `change` it did not dispatch itself (module code setting `.val()`, appending an `<option selected>`) and `form.reset()` (the `reset` event of the form) re-read the options. `disabled` and the `is-invalid` class of the `<select>` are mirrored onto the field (a `MutationObserver` on those attributes), so `yii.activeForm`'s error state shows on the picker.
- **On unmount** (the `MutationObserver` of the Vue runtime) restores the `<select>` (class, `tabindex`, `aria-hidden`, `autofocus`) if it is still in the document, so re-binding is idempotent.

A module that changes a picker from JS changes the `<select>` and dispatches `change`. `humhub.modules.ui.picker` offers helpers that do exactly that, for core code and modules alike:

| Function | Does |
|---|---|
| `getItems(select)` | The chosen items, read from the selected `<option>`s (`{ id, text, subText, image }`) |
| `setItems(select, items)` | Makes `items` the selection (options created as needed), dispatches `change` |
| `addItem(select, item)` / `removeItem(select, id)` | Adds / removes one item, dispatches `change` |

The action `ui.picker.selectSelf` (target: the `<select>`) adds the current user (`user` JS config: `guid`, `text`, `image`) — what `UserPickerField::selfSelect()` renders.

## PHP

### `BasePicker`

Kept: every property, `getItemText()`, `getItemImage()`, `getItemKey()`, `loadItems()`, `getSelectedOptions()`, `getUrl()`, the `_add:` contract. Changed:

- `run()` renders the `<select>` of the [markup contract](#markup-contract) with `data-ui-picker`; `$jsWidget` is null (no JS widget any more); `getData()` keeps its legacy keys (see below). `getData()` returns the `data-picker-*` attributes and the `data-picker-config` array; `getAttributes()` no longer sets `data-tags`, `style`, `title`.
- `getItemImage()` may return a string (as today), a `Stringable` (an `AssetImage`, cast to its URL) or an image description array.
- New `getItemSubText($item): ?string` (default `null`) → `data-sub-text`.
- New `getSource(): ?array` — `null` by default (the legacy `getUrl()`, or static options when the subclass unsets `picker-url` as `MultiSelect` does); a subclass returns `['source' => '<name>', 'params' => [...]]`. A customized picker always stays legacy — `$url` set, `$defaultRoute` changed or `getUrl()` overridden by a subclass —, so a module restricting a picker through its own endpoint keeps it.
- New properties `public bool $listed = false`, `public int $suggested = 0`, `public ?int $maxResults = null` (null: 8 for a remote source, all for static options).
- `getData()` keeps its legacy keys (`placeholder`, `no-result`, `maximum-selected`, `picker-url`, … — subclasses in modules override them); the new final `getPickerData()` translates them into the `data-picker-*` attributes and `data-picker-config`, a key it does not know passing through as `data-<key>`.
- `lockedItems` render `data-locked` on their options and `lockedIds` in the config; `disabledItems` → `disabledIds`.
- No asset of its own: the bridge `humhub.ui.picker.js` stays in `CoreExtensionAsset`, `PickerInput` is in the core Vue bundle; the Select2 assets go.

### The pickers

| Widget | Source | Image | Sub text | Defaults kept |
|---|---|---|---|---|
| `UserPickerField` | `user` (searching all users, as `/user/search/json` did); legacy when customized (see `getSource()`) | `user` | `displayNameSub` | `itemKey` `guid`, `minInput` 3 |
| `SpacePickerField` | `space` (`scope` `all`); legacy when customized | `space` | description | `itemKey` `guid`, `minInput` 3 |
| `ContentContainerPickerField` | legacy (`$url`) | `user`/`space` by record | as above | — |
| `TopicPicker` | `topic` (`containerId` of its container); legacy when `$url` is passed in (today `init()` fills `$url` itself — that default no longer counts) | `color` / `icon` | — | `allowNew` per permission as today, `maxInput` 20, `defaultResults` from `showDefaults` |
| `ContentTagPicker`, `ContainerTagPicker` | legacy | `color` (as today) | — | — |
| `GroupPicker` | static (`$items`) | `icon` `users-group` | — | `maxSelection` 1 for `parent` |
| `ContentTypePicker` | static | `icon`: the type's icon (resolved as today) | — | — |
| `MultiSelect` | static | from `getItemImage()` | — | — |
| `IconPicker` | named source `icon` (`lib` param, default Tabler `ti`): names from the `ui.picker` JS config (`iconNames.<lib>`, comma-separated), once per page; the `<select>` holds the current value only (kept when it is no name of the library) | `icon` | — | single, `maxResults` 50, `minInput` 0 |

`IconPicker` drops `kartik\select2\Select2` and extends `BasePicker` (single, on the named source `icon`).

### `Html::dropDownList()`

`Html::getDropDownListOptions()` sets `data-ui-picker` and `data-picker-search` (from `minimumResultsForSearch`, default 5, counted without the `prompt`; `0` with `data-picker-allow-new`). The `data-ui-select2-allow-new` option becomes `data-picker-allow-new`; `data-ui-select2-new-sign` goes. `CountrySelect` and the core views setting `data-ui-select2` switch to `data-ui-picker`.

### Server endpoints

- `/api/v2/user/picker` answers the short user shape plus `displayNameSub` (only this endpoint — `UserSerializer::short()` stays as it is, the sub title costs a profile lookup per user).
- `space\widgets\Chooser::getSpaceResult()` — the answer of `/space/browse/search-json` and of the move-content space search — gives `image` as a space image description (`['type' => 'space', 'imageUrl' => …, 'name' => …, 'color' => …]`) instead of `Image::widget()` HTML, and `text` next to `title`. `/user/search/json` answers image URLs already and stays.
- No new endpoint: user, space and topic pickers use the existing `/api/v2` lists.

## Shared combobox

`protected/humhub/vue/picker/comboboxMixin.js` (an Options API mixin, as `form/fieldMixin.js`) holds what `FilterPicker`, `PickerFilterControl` and `PickerInput` share: open state, active option, moving it (`moveTo`, scrolling it into view), the outside click, and the list positioning (`drawerPosition.js`: fixed, flip, visible area). Each component keeps its own `onKeydown` and search, which differ in what Enter, Escape and Backspace do. `FilterPicker` and `PickerFilterControl` are moved onto it; their behaviour does not change except for the list positioning, which they gain. Internal files in `protected/humhub/vue/picker/`: `comboboxMixin.js`, `drawerPosition.js`, `sources.js` (static, legacy, the source registry), `images.js` (the string reading and the image registry), `PickerImage.vue`, `nativeSelect.js` (reading and writing the `<select>`). `registerPickerSource` and `registerPickerImage` are exported by `humhub.vue` next to `registerFilterType`.

## Styling

- New `resources/scss/_picker.scss`. The `.select-menu`, `.picker` and `.picker-*` rules move there from `_filter-bar.scss` (they are no longer filter-only); `_filter-bar.scss` keeps only what is the filter bar's own. `_select2.scss` is deleted.
- Built on Bootstrap and the existing classes of [Select & Pickers](../ui/select-pickers.md): the field is a `.form-control.picker-field`, the list a `.dropdown-menu.select-menu-drawer` of `.dropdown-item.select-menu-option`s. New parts, in the same naming:

  | Class | Element |
  |---|---|
  | `.picker-chip-image` | A chip's image; every chip carries `data-value` (its item's `id`) for item-specific styling |
  | `.picker-option-image`, `.picker-option-text`, `.picker-option-subtext` | A result row: image, label, sub text |
  | `.picker-list`, `.picker-list-item`, `-image`, `-text`, `-remove` | The chosen items of a `listed` picker |
  | `.picker-color` | A colour dot |
  | `.picker-value-image` | The image of the chosen item of a single picker / enhanced select in the closed field |

- Sizes, colours and spacing come from Bootstrap and HumHub variables (`$input-*`, `$dropdown-*`, `$spacer`, `$border-radius`, the existing `$picker-chip-*`); new Sass variables only where a value is the picker's own (`$select-menu-field-height`, `$select-menu-icon-*`, `$picker-color-size`, `$picker-list-item-bg`). Nothing is taken over from the design prototype's CSS; where the design asks for a value no token gives, it is listed as an open question in `docs/ui/select-pickers.md` instead of hard-coded.

## Removed

- `humhub\assets\Select2Asset`, `Select2StyleAsset`, `Select2SearchInputPlaceholderAsset`, `resources/js/select2-searchInputPlaceholder(.min).js`, `resources/scss/_select2.scss`; the stale "select2" docblocks of `JuiBootstrapBridgeAsset` and `PjaxAsset` are corrected.
- The old `humhub.ui.picker.js` (Select2 `Picker` widget) — replaced by the bridge of the same file name.
- `humhub.ui.additions`: the `select2` addition and the dropdown-below adapter.
- Select2 handling in `ui.modal` (focus; the `select2-input` key handling), `content.form` (`.select2-selection` selectors), `stream.wall` (the picker widget calls → the `ui.picker` helpers, `afterInitSelect2` → the `<select>`'s `change`), `ui.filter` (`PickerInput` reads the `<select>`), `cards.js` (the submit handler copying `data-id` into the value goes: the picker's option values are the ids), `admin/views/group/edit.php` (`select2:select` → `change`), `admin/views/logging/index.php` (the level chip colours select `.picker-chip[data-value="<level>"]`); `humhub.user.picker.js` and `UserPickerAsset` (the `selectSelf` action moves to `ui.picker`).
- `npm-asset/select2` (`composer.json`, `package.json`) and the `kartik\select2\Select2Asset` mapping in `config/web.php`; `admin/views/setting/mobile_app.php` no longer uses `kartik\widgets\Select2`. `.github/release/exclude.txt` is checked for the removed paths.

## Tests

- **vitest**: `comboboxMixin` and `drawerPosition` (the existing `FilterPicker`/`PickerFilterControl` suites unchanged and green); `PickerInput` (single, multiple, `listed`, locked, disabled, `maxSelection`, `allowNew` and `_add:`, suggestions, highlighting, feedback rows, keyboard); `sources` (legacy shape, ordering, cache, failure); `images` (string reading, HTML refused, registry); `nativeSelect` (read, write, `change`, external change, `form.reset()`, `is-invalid`/`disabled` mirroring, idempotent re-binding, failed mount leaves the `<select>`).
- **PHP unit**: `BasePicker` markup and config, single vs multiple, locked/disabled, `getSource()` of every picker, image descriptions; `Html::getDropDownListOptions()`; `Chooser::getSpaceResult()`; the `displayNameSub` of the user picker answer (api suite).
- **Acceptance**: `AcceptanceTester::selectFromPicker($selector, $search)` and `selectUserFromPicker()` keep their signatures and drive the new markup (`$selector` is the `<select>`; the input is `$selector + [data-vue-component] .picker-input`, the option `.select-menu-option`), so the core and module acceptance tests run unchanged. Existing picker scenarios (invite, move content, topics, group edit, content type filter) stay green.

## Documentation

- `docs/ui/select-pickers.md`: open questions 1–3 answered (one Vue implementation; the enhanced select everywhere `Html::dropDownList()` is used; minimum characters per widget, defaults as above), "Implementation in core" updated, the "Listed" variant named after `listed`.
- [Form suite](ui-js-vuejs-forms.md): `PickerField` in the component reference. [Components](ui-js-vuejs-components.md): `PickerInput`, `registerPickerSource`, `registerPickerImage`. [Roadmap](ui-js-vuejs-roadmap.md): "Space picker search" removed (the picker uses `GET /api/v2/space?purpose=picker`).
- `module-migrate-1.20.md`: **Removed** — the Select2 JS API (`.select2()`, `$.fn.select2`), its events (`select2:*`, `afterInitSelect2`), `Select2*Asset`, `kartik\select2` (no longer used by core), HTML in picker item images; **Deprecated** — `data-ui-select2` (until 1.21); **Refactored** — `BasePicker` markup and `getSource()`/`getItemSubText()`/image descriptions, `data-picker-*` attributes, listening to `change` on the `<select>`.
- `CHANGELOG.md`: one `Enh` line.

## Module migration

Done after the core PR with `/humhub:refactor-modules`, from the module search of 2026-11-03: the modules calling `.select2(…)` (8), listening to Select2 events (7), setting `data-ui-select2` (15, working through the alias until 1.21), and `cuzy-app/ecommerce` (HTML item images). Subclasses of `BasePicker` (wiki, mail, lets-meet, matchmaking, translation, cuzy-app modules) need no change unless they return HTML images.
