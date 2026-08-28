# Extending Vue.js Islands

> Part of the [Vue.js integration](ui-js-vuejs.md) documentation. This chapter covers how a module extends another module's island from the outside: extension slots, menu entries, the serializer extension event pattern, domain events on the bus, and the (planned) component override mechanism. For motivation, goals, constraints and the overall architecture, see the [overview](ui-js-vuejs.md).

## Extension slots

The Vue analog of PHP widget stacks: a host component renders a named extension point via `<ExtensionSlot>`, and other modules hook into it by name without forking the host's template — the same relationship `\humhub\widgets\BaseStack` subclasses have to the widgets they stack, translated to islands.

```html
<!-- inside CommentEntry.vue -->
<ExtensionSlot name="comment.links" :context="{ comment }" />
```

```js
// another module's own vue/index.js (see "Module file layout" in the components chapter)
import { register, registerSlotComponent } from '@humhub/vue';
import ReactionLink from './ReactionLink.vue';

register('ReactionLink', ReactionLink);
registerSlotComponent('comment.links', 'ReactionLink', { sortOrder: 150 });
```

`ExtensionSlot` renders every component registered for its name (via `registerSlotComponent(slotName, componentName, {sortOrder})`), passing `context` down as props to each. Entries render in `sortOrder` order (default `100`), then registration order for ties. Slot names follow the same `<module>.<region>` convention already used elsewhere: `comment.links`, appended after the core Reply/Like links in `.wall-entry-controls`.

**Registration order is unconstrained** — `registerSlotComponent()` does not require `componentName` to be registered yet, and `register()` does not require any slot referencing it to exist yet. Whichever half arrives second, `ExtensionSlot` picks it up reactively (no remount). A slot with nothing registered — or nothing *currently registered* — renders nothing: no placeholder, no warning; modules stay entirely optional.

## Menu entries

The array-of-entries counterpart to the free-form slot above, modeled on the server-side `humhub\modules\ui\menu\widgets\Menu` API module devs already know (`addEntry()`/`removeEntry()`, entries with an `id` and a `sortOrder`). `DropdownMenu` (see [Components: core component set](ui-js-vuejs-components.md#core-component-set)) grows a `menuId`/`entries` mode: a menu identifies itself with a `menuId`, contributes its own built-in items as `entries`, and other modules add, override or remove items by `id` through the registry — instead of forking the host's markup, the same relationship `ExtensionSlot` has to a slot, but for an *ordered, removable list of items* rather than a free-form fragment.

```html
<!-- inside CommentControls.vue -->
<DropdownMenu :toggle-aria-label="label" menu-id="comment.controls" :entries="entries" :context="{ comment }" />
```

```js
// another module's own vue/index.js
import { registerMenuEntry, removeMenuEntry } from '@humhub/vue';

registerMenuEntry('comment.controls', {
    id: 'report',
    label: 'Report',
    icon: 'flag',
    sortOrder: 150,
    condition: (context) => !context.comment.extensions.reportcontent?.reported,
    onClick: (context) => reportComment(context.comment.id),
});

// removeMenuEntry('comment.controls', 'edit'); // suppresses a built-in or another module's entry by id
```

**Entry shape** (the second argument to `registerMenuEntry()`, and what a menu's own `entries` prop holds):

| Field | Type | Required | Notes |
|---|---|---|---|
| `id` | `string` | yes | Unique per menu. Registering the same `(menuId, id)` again **replaces** the existing entry in place — the supported override mechanism (unlike `registerSlotComponent()`'s "first registration wins"). |
| `label` | `string` \| `(context) => string` | unless `component` given | Static or context-derived text. |
| `icon` | `string` | no | An `Icon::get()`-style icon name (e.g. `'pencil'`), rendered as `<i class="fa fa-<icon>">`. |
| `sortOrder` | `number` | no (default `1000`) | Ascending, like PHP menu entries. |
| `condition` | `(context) => boolean` | no | Omit to always show. |
| `onClick` | `(context) => void` | no | Ignored when `component` is set. |
| `component` | `string` | unless `label` given | A name registered via `register()` — an escape hatch for fully custom rendering; the component receives a single `context` prop (not spread, unlike `ExtensionSlot`'s `v-bind`). When set, `label`/`icon`/`onClick` are ignored. |
| `url` | `string` | no | Renders a real `href` instead of `#`, and the click is not swallowed, so middle-click and "open in new tab" work. An entry with its own `onClick` takes precedence. |
| `htmlOptions` | `object` | no | Bound onto the anchor — how a legacy `data-action-click` entry keeps working once a client renders it. A `href` here loses to `url`. |
| `divider` | `boolean` | no | Renders `<hr class="dropdown-divider">` instead of a link; the client-side counterpart of `DropdownDivider`. |
| `html` | `string` | no | Raw markup that becomes the whole `<li>`, injected with `v-html` and run through the UI additions. The escape hatch for a server entry that cannot be described — see [Server-described entries](#server-described-entries-and-contentcontrols). Cannot be labelled, conditioned or overridden. |

**Resolution** (recomputed reactively whenever anything registers/removes, so a currently-mounted menu updates without remounting):

1. Start from the menu's own `entries` (built-ins, in their given order), then append registry entries for `menuId` that don't share an `id` with a built-in.
2. A registry entry whose `id` matches a built-in's `id` **overrides** it, in the built-in's own position (not moved to the end).
3. Drop any entry — built-in or registry — whose `id` was passed to `removeMenuEntry(menuId, id)`.
4. Drop entries whose `condition(context)` is falsy.
5. Drop `component` entries whose component is not (yet) registered (same "stay optional" rule `ExtensionSlot` follows).
6. Sort by `sortOrder` ascending; entries sharing a `sortOrder` keep their step-1/2 order — **built-ins before registry entries**.

**Removals are permanent and win over later registrations of the same id** — there is no "un-remove". A module that decides an id should never appear again does not have to race load order against a module that might (re-)register it afterwards, in either direction. A module that needs a *toggleable* presence should use `condition` on its own entry instead of removing and re-registering.

### `comment.controls`

`CommentControls.vue` (the comment entry's `⋮` dropdown) exposes this menu id. Built-in entries:

| id | Shown while | Behavior |
|---|---|---|
| `edit` | `canEdit` | Opens inline edit. |
| `delete` | `canDelete` | Deletes the comment; opens `CommentDeleteModal` — in its admin mode (reason + notify-the-author fields) when `canAdminDelete` is also set, as the plain confirm otherwise. One entry covers both, since `canAdminDelete` is only ever derived on top of `canDelete`. |

The permalink item is **not** part of this menu — it carries legacy `data-action-click`/`data-content-permalink*` attributes for a delegated document click handler rather than a Vue click handler, which the entry descriptor shape has no room for; it stays a hand-rendered `<li>` in `CommentControls.vue`'s default slot, rendered ahead of the resolved `comment.controls` entries.

### Server-described entries and `ContentControls`

The two mechanisms above assume the extending module ships JavaScript. The platform's
oldest and widest menu extension point does not: `WallEntryControls` — the `⋮` menu of a
content record — has been extended for years by modules adding **widget** entries in a
`WallEntryControls::EVENT_INIT` handler (`topic` in core, plus `reportcontent`,
`share-between-humhub`, `polls`, and others outside it). Breaking every one of them the way
the comment island's own controls menu did is defensible once; doing it again for every
module that moves a content list into Vue is not.

`humhub\modules\content\vue\ContentControls.vue` (`ContentVueAsset`) is the island form of
that menu, and it merges **three** sources:

1. **The host island's own entries** — passed as `entries`, in the shape above, with real
   Vue click handlers. A file browser's Download/Rename/Move live here.
2. **Server-described entries** — the resolved `WallEntryControls` stack of the record,
   fetched from `GET /api/v2/content/<id>/controls` when the menu is opened.
3. **The client registry** — `registerMenuEntry('content.controls', …)`, resolved last, so
   it can override (same `id`) or remove entries from either of the other two.

```html
<!-- inside a module's own row component -->
<ContentControls
    :content-id="item.contentId"
    view-context="browser"
    :entries="ownEntries"
    :context="{ item }"
/>
```

**Right-click.** A row that owns a `ContentControls` can raise it where the cursor is, which
is what the legacy `$.fn.contextMenu` did for server-rendered lists:

```html
<div class="row" @contextmenu="onContextMenu">
    <ContentControls ref="controls" :content-id="item.contentId" … />
</div>
```

```js
onContextMenu(event) {
    // Ctrl+right-click stays the browser's, as it always was.
    if (event.ctrlKey || event.target.closest('.dropdown-menu')) {
        return;
    }
    event.preventDefault();
    this.$refs.controls.open(event);
}
```

**Describing a server entry.** `humhub\modules\ui\menu\MenuEntry::describe()` returns the
descriptor for an entry, or `null` when the entry can only be rendered. `MenuLink` and
`DropdownDivider` describe themselves; a `WidgetMenuEntry` delegates to its widget when that
widget implements `humhub\modules\ui\menu\DescribableWidget`.

`WallEntryControlLink` implements it, which covers the whole family of control links that
extend it — `EditPageLink` (wiki), `ShareLink` (share-between-humhub), `ContentTopicButton`
(topic) — **with no change in those modules at all**. The one restriction is load-bearing: the
base implementation refuses to describe a subclass that overrides `renderLink()`, because
such a subclass builds its label or url inside the render (as `ContentTopicButton` and
`EditPageLink` both do) and describing it from the base class' properties would silently
produce an empty label or a dead `#` link. A subclass in that position describes itself:

```php
class ContentTopicButton extends WallEntryControlLink
{
    public function renderLink()
    {
        return $this->buildLink();          // one definition …
    }

    public function describeMenuEntry(): ?array
    {
        $link = $this->buildLink();         // … used by both paths, so they cannot drift

        return [
            'id' => 'topics',
            'label' => (string)$link->label,
            'icon' => MenuLink::describeIcon($link->icon),
            'htmlOptions' => $link->options,
        ];
    }
}
```

The descriptor's `htmlOptions` are bound straight onto the client-rendered anchor, which is
what keeps a legacy `data-action-click` entry working: the delegated document handler in
`humhub.action.js` reads the attribute off the DOM whether the server or Vue put it there.

**The HTML escape hatch.** An entry whose widget renders its own view and cannot be described
(`polls`' `CloseButton`, say) is rendered server-side and shipped as `html`, which the island
injects with `v-html` and runs the UI additions over. Nothing breaks and no module has to act
immediately — but such an entry is a dead end: a client cannot label, condition, reorder
beyond `sortOrder`, override or remove it. **The path is deprecated**; every delivery logs a
warning naming the widget class. Implement `DescribableWidget` (or migrate to
`registerMenuEntry()`) before it is removed.

**Lazily, per menu.** Nothing is fetched until a menu is opened, because everything in that
response depends on who is asking — `canEdit`, `canDelete`, which modules contribute what.
That is the same reason `canEdit`/`canDelete` are not in a comment payload (see
[HTTP API framework](concept-api.md), "Caller context is not part of a payload"): a list of
50 rows costs zero requests until someone opens a menu. The response also carries a
`capabilities` object (`canEdit`, `canDelete`, `canAdminDelete`, `canPin`, `canArchive`,
`canMove`), which the host gates its own native entries on through their `condition`, instead
of re-implementing the rules client-side.

**`viewContext`** picks the server-side render-options profile the same place would have used
when server-rendered (`stream`, `detail`, `modal`, …), so a menu inside a module's own UI is
not offered stream-only actions.

**`.nav-pills preferences` is a look, not neutral markup.** `DropdownMenu`'s default root
carries it, and two core stylesheets act on it:

- `_nav.scss` fills `.nav-pills .dropdown-menu` with `var(--bs-primary)` and no border — that
  IS the platform's content-context-menu appearance, so keeping the default is right for a
  context menu and wrong for anything else. A dropdown BUTTON in a toolbar must pass its own
  `rootClass` (and then its own label through the `toggle` slot, since the meatball icon is an
  `::after` on `.preferences`), or it renders as an empty primary-filled block.
- `_nav.scss` also positions `.nav-pills.preferences` `absolute; right: 10px; top: 10px`,
  which pins a stream or comment entry's menu to its corner. A menu in normal flow — a list
  row — has to reset that, or it lands on top of whatever it should sit beside.

**Inside a `.hh-list` row there was a third problem, now fixed in core.** `_list.scss`
colours a row's anchors (`.hh-list > div a`) at specificity 0,2,2, exactly tying with
`_nav.scss`'s `.nav-pills .dropdown-menu li a` — and `list` is imported after `nav`, so the
list won on source order alone and a context menu inside a list got the LIST's text colour on
the MENU's primary background. `_list.scss` now excludes `a:not(.dropdown-item)`: an anchor
that is a dropdown item belongs to the menu, not the row.

The files module's `resources/css/cfiles.css` is the worked example for the first two.

### The like link in your own list

`<LikeButton>` (`LikeVueAsset`) is the platform's own like link, and a migrated list should
render it rather than reimplement one. It takes a `recordId` — the platform-wide record id from
`humhub\models\RecordMap`, NOT a content id — and, optionally, `likeCount`/`currentUserLiked`.
Pass those two and the button renders complete without a request; leave them off and every row
fetches its own state.

Serialize them for the whole page rather than per row: `LikeSerializer::statesForRecords()`
answers a page in two grouped queries, keyed by record id. The state is per caller, so it
belongs in its own section of a listing payload, not inside rows that want to stay
caller-neutral (see [HTTP API framework](concept-api.md)) — the files module's file browser is
the worked example.

### Menu entries vs. extension slots

Both let a module hook into a host component without forking its template, but they solve different problems:

- **Menu entries** (`DropdownMenu`'s `menuId`/`entries`) — a *data-driven, orderable, removable list of items with a stable identity per item*: a dropdown/context menu, a toolbar. Reach for this when the extension point is "one more action alongside these other actions" — a module can inject, override, or remove a specific item by id.
- **Extension slots** (`ExtensionSlot`) — a *free-form UI fragment* with no inherent structure beyond "render here": a link in a row of links, a badge, a panel. There is no override/removal by id — only "is this component currently registered for this slot".

`comment.controls` (a menu — Edit/Delete plus whatever a module injects) and `comment.links` (a slot — Reply/Like plus whatever a module appends) on the very same comment entry illustrate the split: the `⋮` menu is a list of discrete actions a module might want to reorder, replace or suppress; the inline links row is just "append your own link here".

## Serializer extension events

A component reached through a slot commonly needs data the host itself doesn't otherwise expose. Since the islands are fed by the platform's HTTP API (see [HTTP API framework](concept-api.md)), that extension point sits in the serialization layer: `humhub\components\api\SerializeEvent`, fired once per serialized batch of one record type — a window of comments (roots plus loaded reply previews), or a single create/update/view response. The event name is shared across record types, so a handler filters on `$event->type`. A module attaches in its `config.php` and reads the result back out of `context.comment.extensions` on the JS side:

```php
// a module's config.php
'events' => [
    [SerializeEvent::class, SerializeEvent::EVENT_SERIALIZE, [Events::class, 'onApiSerialize']],
],
```

```php
// the module's Events.php
public static function onApiSerialize(SerializeEvent $event): void
{
    if ($event->type !== Comment::class) {
        return;
    }
    foreach ($event->records as $comment) {
        $event->addData($comment->id, 'reportcontent', ['reported' => ReportContent::isReported($comment)]);
    }
}
```

```vue
<!-- ReactionLink.vue -->
<template>
    <a href="#" @click.prevent="onClick">{{ label }}<span v-if="comment.extensions.reportcontent?.reported"> (reported)</span></a>
</template>
<script>
export default {
    props: { comment: { type: Object, required: true } },
    /* ... */
};
</script>
```

Each serialized comment carries the accumulated result under its own `extensions` key, namespaced by the attaching module (`{}` when nothing attached anything) — one query for the whole batch rather than one per comment.

**Attach caller-neutral data only — and expect it to be cached.** The serialized payloads are cached server-side per content (`comment\services\CommentPayloadCache`), retired when a comment changes; data you attach lives in that cache with it, so anything that changes independently of the comment is stale until the TTL expires (default one hour).  The payload is identical for every reader who may see the content — that is what allows one serialization to be cached and served to all of them (see [HTTP API framework](concept-api.md), "Caller context is not part of a payload"). Data that depends on WHO is asking ("did *I* already report this?") would make the cached payload wrong for the next reader, so it does not belong here: fetch it from your own module's endpoint, in the menu entry's own component or in the `onClick` handler — the module needs an endpoint for the action itself anyway. Core does the same for the two caller-specific values its own UI needs (like state, edit/delete permissions).

## Domain events on the bus

Beyond named extension points, islands can react to domain-specific occurrences that other islands (or legacy code) emit on the shared event bus, via `useEvents()` (see [Components: bridge layer](ui-js-vuejs-components.md#bridge-layer-composables)). The comment island, for example, fires `humhub:modules:comment:live:NewComment` when a live update inserts a comment (see the comment-section pilot in the [overview](ui-js-vuejs.md#pilots-and-migration-path)). Unlike `ExtensionSlot`, this is unstructured, ad hoc pub/sub: any island — or any legacy `humhub.module` — can listen, and the emitting component makes no registration-time guarantee about who's listening. Reach for a domain event when reacting to something happening elsewhere; reach for an extension slot when rendering *into* a specific place in another component's output.

## Component override

**Status: planned, not implemented.** Themes and modules can already override PHP widget views (see [View and mail overrides](theme-views.md)); the equivalent for islands — a theme or module replacing a specific named component's registration with its own implementation, the same relationship a view override has to a widget's default view — has no implementation yet. The registry's current "first registration wins" rule (see [Components: component registry](ui-js-vuejs-components.md#component-registry)) is what would need to change to support this; no design or timeline is attached.

## Migrating a legacy widget-stack extension

`humhub/reportcontent` is the real, documented case this pattern replaces: it used to hook `humhub\modules\comment\widgets\CommentControls::EVENT_INIT` to inject a "Report" entry into each comment's `⋮` menu — a PHP widget stack extension point. Since comment entries no longer render through a per-comment PHP widget pass (`CommentEntry.vue` renders straight from JSON), that hook stopped firing (see the `Unreleased` section of [the module migration guide](module-migrate.md) for the full breaking-change record). Migrating a module like it to the Vue island means combining [menu entries](#menu-entries) above with a serializer event:

1. Register a `comment.controls` menu entry: `registerMenuEntry('comment.controls', { id: 'report', label: 'Report', sortOrder: 150, onClick: (context) => reportComment(context.comment.id) })` (see [Menu entries](#menu-entries) above) — this alone gets the module's own item rendering again, in the right place. A plain `label`/`onClick` entry is enough here; reach for the `component` escape hatch only if the item needs markup the descriptor can't express (an icon plus a "(reported)" suffix, say, still fits `label` as a function of `context`).
2. If the item needs data beyond what `context` already carries (here: whether the comment is already reported), attach `humhub\components\api\SerializeEvent` in `config.php` and add it under a namespaced key via `$event->addData(...)` (see [Serializer extension events](#serializer-extension-events) below).
3. Read that data back out of `context.comment.extensions.reportcontent` inside the entry's `label`/`condition`/`onClick` (or a `component` entry's own props) — no other change to the module's controller or business logic is needed; only the injection point moves from a PHP widget-stack event to a menu-entry registration plus (optionally) a serializer event.
