<template>
    <section class="c-page-toolbar" :aria-labelledby="title ? titleId : null">
        <div v-if="title || actions.length || $slots.actions" class="c-page-toolbar__header">
            <component :is="titleTag" v-if="title" :id="titleId" class="c-page-toolbar__title">{{ title }}</component>
            <div v-if="actions.length || $slots.actions" class="c-page-toolbar__actions">
                <a
                    v-for="action in actions"
                    :key="action.id"
                    v-bind="action.htmlOptions || {}"
                    :class="['btn', `btn-${variantOf(action)}`, 'c-icon-button']"
                    :href="action.url"
                    :aria-label="action.label"
                    :title="action.label"
                    :data-action-id="action.id"
                    @click="onAction($event, action)"
                ><i :class="['ti', `ti-${action.icon}`]" aria-hidden="true"></i></a>
                <slot name="actions"></slot>
            </div>
        </div>
        <slot></slot>
    </section>
</template>

<script>
import { modal } from '@humhub/vue';

let uid = 0;

const VARIANTS = ['secondary', 'accent', 'primary'];

/**
 * The upper box of a page (after the design system's `c-page-toolbar`): a card, at most 1440px
 * wide and centred, with a header row — the `title` (rendered as `titleTag`, default `h1`; the
 * toolbar is labelled by it) and the `actions` slot right-aligned beside it (typically
 * `.btn.c-icon-button` buttons) — and below it the default slot, typically a `FilterBar`. The
 * header is only rendered with a title or actions. Pure layout: it loads nothing and holds no
 * state, so any page can use it — a card directory (`CardDirectory` renders one) as well as a
 * module page with a view of its own below it (e.g. a file list).
 *
 * - `actions` prop: `[{ id, icon, label, url, modal?, variant?, htmlOptions? }]` — toolbar actions
 *   as data (typically a PHP menu's entries, see `humhub\widgets\menu\Menu::getEntriesData()`),
 *   rendered as icon links (`a.btn.btn-<variant>.c-icon-button`; `variant` one of `secondary` —
 *   the default, also for an unknown value —, `accent`, `primary`;
 *   the Tabler icon `ti ti-<icon>`, `aria-label` and `title` = `label`, `data-action-id` = `id`)
 *   before the `actions` slot content. `modal: true` opens `url` in the global modal
 *   (`modal.load(url)` of the bridge) instead of following the link; `htmlOptions` are extra
 *   attributes of the link (a `class` is added to the button classes).
 * - `actions` slot: further header actions of the page's own (e.g. a button with state), after
 *   the `actions` prop's.
 *
 * @since 1.20
 */
export default {
    name: 'PageToolbar',
    props: {
        title: { type: String, default: '' },
        titleTag: { type: String, default: 'h1' },
        /**
         * Header actions as data: `[{ id, icon, label, url, modal?, variant?, htmlOptions? }]`.
         * @since 1.20
         */
        actions: { type: Array, default: () => [] },
    },
    data() {
        return {
            titleId: `page-toolbar-title-${++uid}`,
        };
    },
    methods: {
        /**
         * The button variant of an action: `secondary` unless it names another known one.
         * @since 1.20
         */
        variantOf(action) {
            return VARIANTS.includes(action.variant) ? action.variant : 'secondary';
        },
        /**
         * Opens a `modal: true` action in the global modal; any other action is a plain link.
         * @since 1.20
         */
        onAction(event, action) {
            if (action.modal) {
                event.preventDefault();
                modal.load(action.url);
            }
        },
    },
};
</script>
