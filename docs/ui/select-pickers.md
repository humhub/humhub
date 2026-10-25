# Select & Pickers

> Found in the design: settings (selects), filter bars, Create task (assignees, space, topics),
> Share (people), the component demo page (user, space, topic, group and content type pickers)
> · Core: PHP `BasePicker` (select2) with `UserPickerField`, `SpacePickerField`,
> `ContentTagPicker`, `MultiSelect`; Vue `SelectField` (native `<select>`), `FilterSelect`,
> `FilterPicker`, `PickerFilterControl` (filters only)
> · Technology: **Both**

Two controls for choosing:

- a **select** picks one value from a **short, fixed list** (sort order, visibility, page size)
- a **picker** **searches records** (people, spaces, topics, groups, content types) and picks one or
  several of them

Both share the [Form Fields](form-fields.md) anatomy and states.

![Select and pickers](images/select-pickers.svg)

## Select

- **Closed:** value plus chevron. Empty selects show a placeholder ("Select…").
- **Open:** the list opens below the field, or **above** when there is no room. It is never clipped
  by a dialog or card. The chosen option is highlighted with a check.
- **In filter bars:** the first option is "All" (no filter), and a set value gets a × to clear it.
  In forms, a select that always has a value has no ×.
- **Long lists** (more than about 10 options, or options that come from the server) become a
  picker with search.

## Picker

- **Search:** results appear after typing (usually from 2–3 characters). Some pickers show
  suggestions before typing (e.g. people of the current space). Matches are highlighted.
- **Results** are list rows: image or icon, name, a subtitle (job title, space description, "Content
  type"). Already chosen records are hidden from the results.
- **Chosen records** become chips in the field (accent, with image and ×). Backspace in the empty
  input removes the last chip.
- **Feedback** in the result list: "Type at least 3 characters", "No matches found", "Maximum
  reached".
- **Keyboard:** ↑/↓ move through results, Enter adds, Escape closes.

## Picker variants

| Variant | Use | Notes |
|---|---|---|
| **Multiple** | Participants, assignees, recipients | Chips in the field, optional maximum |
| **Single record** | Owner, space for a task | Shows the one record with its image, × to clear |
| **Listed** | Share with, members of a long list | Chosen records appear as rows below the field instead of chips |
| **Can create** | Topics, tags | A "Create "roadm"" row adds a new entry from the typed text |
| **Types** | User, space, topic, user group, content type, tag | Each type has its own result row and data source. Modules can register more. |

## Mobile

- The same list below or above the field. No bottom sheet.
- With the on-screen keyboard open, the list flips above the field when the keyboard covers the
  space below, and it is limited to the visible area.
- Chips wrap onto further lines inside the field.

## Accessibility

- **Select:** the trigger is a button (or native `<select>`) labelled by the field's label, with
  `aria-expanded`. Options are `role="option"` with `aria-selected`.
  - **Arrow keys** move through the options, and typing jumps to a matching option.
- **Picker:** WAI-ARIA combobox pattern: input with `role="combobox"`, `aria-expanded` and
  `aria-activedescendant`, results as `listbox`.
- Feedback messages are announced (`aria-live="polite"`).
- Each chip's × is a button labelled "Remove Thomas Bennett".
- The accessible name is always the field's name, never "Select".

## Markup and styles

A native select is Bootstrap's **`.form-select`**. Where native isn't enough (filters, rich
options), the custom listbox **`.select-menu`** is used; a picker is a `.select-menu` whose
button is a field with chips and an input. Class names and SCSS follow
[Styling with Bootstrap 5](bootstrap.md).

| Class | Element |
|---|---|
| `select.form-select` | Native select |
| `.select-menu` | Custom listbox (`FilterSelect`); `.is-open`, `.has-selection`, `.is-loading` |
| `.form-control.select-menu-button`, `.select-menu-value`, `-chevron`, `-clear`, `-spinner` | The button with the chosen value, the chevron, the × that clears it, the spinner |
| `.dropdown-menu.select-menu-drawer` | The listbox, open with `.show`, placed below the field with `data-bs-popper="static"` |
| `.dropdown-item.select-menu-option` | An option: `.active` when chosen (`aria-selected`), `.is-focused` for the option the keyboard is on (`aria-activedescendant`, drawn like hover) |
| `.dropdown-item-text.select-menu-feedback` | "No results", "Loading…" |
| `.picker` | A searchable picker (`FilterPicker`, `PickerFilterControl`) on `.select-menu`; `.is-disabled`, `.is-multiple` |
| `.form-control.picker-field`, `.picker-input` | The field holding chips and the combobox input |
| `.picker-chip`, `-label`, `-remove` | A chosen value |
| `.picker-option-label`, `-count` | An option with a count |
| `.picker-filter`, `-icon`, `-avatar`, `-chip`, `-name`, `-detail` | A record filter (`PickerFilterControl`) |
| `.user-filter`, `.space-filter`, `.topic-filter` | The module types; a type's own parts are `<block>-icon`, `-chip`, `-avatar`, `-option`, `-name`, `-detail` (e.g. `.topic-filter-dot`) |

- **File:** `_filter-bar.scss`, behind `$prev-filter-bar`.
- The custom select's button is a `.form-control`, not a `.form-select`: it is no native
  `<select>`, and it brings its own chevron and clear ×. So filter fields and form fields share
  one look.
- **Sass variables:** `$select-menu-max-height` (the listbox's height before it scrolls),
  `$picker-chip-bg`, `$picker-chip-color`, `$picker-chip-font-size`; the rest is `$input-*` and
  `$dropdown-*`.

## Implementation in core

**Today:**

| Where | What |
|---|---|
| PHP forms | `humhub\widgets\form\BasePicker` (select2) with `UserPickerField`, `SpacePickerField`, `ContentTagPicker`, `MultiSelect`. Native `dropDownList` for selects. |
| Vue forms | `SelectField` renders a native `<select>` (Bootstrap `form-select`). There is no picker for forms yet. |
| Vue filters | `FilterSelect` (listbox with "all" state and ×), `FilterPicker` and `PickerFilterControl` (combobox with search, chips), plus the module types `UserFilterControl`, `SpaceFilterControl` and `TopicFilterControl`. These already follow the design. |

**Gaps:**

| Gap | Proposal |
|---|---|
| No picker for Vue forms | Promote `PickerFilterControl` into a form field (`PickerField`, with `attribute`, errors, the `HumHubForm` contract), with types user, space, topic, group |
| PHP pickers use select2 | Restyle select2 to the new look as a first step. Later, render PHP pickers as Vue islands (`VueWidget`) on the same component, so there is one implementation. |
| Native `<select>` look | Style `form-select` (fill, chevron, states). Use the custom listbox only where native isn't enough (filters, rich options). |
| Can-create and listed modes | Options on the shared picker |

## Open questions

1. **One picker implementation:** move PHP pickers to the Vue picker via `VueWidget`, or keep
   select2 restyled for PHP forms?
2. **Native or custom select in forms:** native `<select>` (best on mobile, simplest), or the
   custom listbox everywhere for a consistent look?
3. **Minimum characters:** one value for all pickers (e.g. 2), or per type?
