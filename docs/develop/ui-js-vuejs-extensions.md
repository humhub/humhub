# Extending Vue.js Islands

> Part of the [Vue.js integration](ui-js-vuejs.md) documentation. This chapter covers how a module extends another module's island from the outside: extension slots, the serializer extension event pattern, domain events on the bus, and the (planned) component override mechanism. For motivation, goals, constraints and the overall architecture, see the [overview](ui-js-vuejs.md).

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

`ExtensionSlot` renders every component registered for its name (via `registerSlotComponent(slotName, componentName, {sortOrder})`), passing `context` down as props to each. Entries render in `sortOrder` order (default `100`), then registration order for ties. Slot names follow the same `<module>.<region>` convention as the two the comment island exposes today: `comment.controls` (inside the entry's `⋮` dropdown — a registered component owns its own `<li><a class="dropdown-item">…` markup, the same contract the dropdown's core items follow) and `comment.links` (appended after the core Reply/Like links in `.wall-entry-controls`).

**Registration order is unconstrained** — `registerSlotComponent()` does not require `componentName` to be registered yet, and `register()` does not require any slot referencing it to exist yet. Whichever half arrives second, `ExtensionSlot` picks it up reactively (no remount). A slot with nothing registered — or nothing *currently registered* — renders nothing: no placeholder, no warning; modules stay entirely optional.

## Serializer extension events

A component reached through a slot commonly needs data the host itself doesn't otherwise expose. The comment island solves this on the serializer side with a matching extension point, `CommentJsonService::EVENT_SERIALIZE_COMMENTS` (a `SerializeCommentsEvent`, fired once per serialized batch — a window of comments, or a single create/update/info response). A module attaches in its `config.php` and reads the result back out of `context.comment.extensions` on the JS side:

```php
// a module's config.php
'events' => [
    [CommentJsonService::class, CommentJsonService::EVENT_SERIALIZE_COMMENTS, [Events::class, 'onSerializeComments']],
],
```

```php
// the module's Events.php
public static function onSerializeComments(SerializeCommentsEvent $event): void
{
    foreach ($event->comments as $comment) {
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

## Domain events on the bus

Beyond named extension points, islands can react to domain-specific occurrences that other islands (or legacy code) emit on the shared event bus, via `useEvents()` (see [Components: bridge layer](ui-js-vuejs-components.md#bridge-layer-composables)). The comment island, for example, fires `humhub:modules:comment:live:NewComment` when a live update inserts a comment (see the comment-section pilot in the [overview](ui-js-vuejs.md#pilots-and-migration-path)). Unlike `ExtensionSlot`, this is unstructured, ad hoc pub/sub: any island — or any legacy `humhub.module` — can listen, and the emitting component makes no registration-time guarantee about who's listening. Reach for a domain event when reacting to something happening elsewhere; reach for an extension slot when rendering *into* a specific place in another component's output.

## Component override

**Status: planned, not implemented.** Themes and modules can already override PHP widget views (see [View and mail overrides](theme-views.md)); the equivalent for islands — a theme or module replacing a specific named component's registration with its own implementation, the same relationship a view override has to a widget's default view — has no implementation yet. The registry's current "first registration wins" rule (see [Components: component registry](ui-js-vuejs-components.md#component-registry)) is what would need to change to support this; no design or timeline is attached.

## Migrating a legacy widget-stack extension

`humhub/reportcontent` is the real, documented case this pattern replaces: it used to hook `humhub\modules\comment\widgets\CommentControls::EVENT_INIT` to inject a "Report" entry into each comment's `⋮` menu — a PHP widget stack extension point. Since comment entries no longer render through a per-comment PHP widget pass (`CommentEntry.vue` renders straight from JSON), that hook stopped firing (see the `Unreleased` section of [the module migration guide](module-migrate.md) for the full breaking-change record). Migrating a module like it to the Vue island means combining the two mechanisms above:

1. Ship a `ReportLink.vue`-shaped component and register it into the menu's slot: `registerSlotComponent('comment.controls', 'ReportLink', { sortOrder: 150 })` (see [Extension slots](#extension-slots) above) — this alone gets the module's own `<li><a class="dropdown-item">` entry rendering again.
2. If the component needs data beyond what `context` already carries (here: whether the comment is already reported), attach `CommentJsonService::EVENT_SERIALIZE_COMMENTS` in `config.php` and add it under a namespaced key via `$event->addData(...)` (see [Serializer extension events](#serializer-extension-events) above).
3. Read that data back out of `context.comment.extensions.reportcontent` inside the component — no other change to the module's controller or business logic is needed; only the injection point moves from a PHP widget-stack event to a slot registration plus (optionally) a serializer event.
