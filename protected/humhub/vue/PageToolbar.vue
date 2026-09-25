<template>
    <section class="c-page-toolbar" :aria-labelledby="title ? titleId : null">
        <div v-if="title || $slots.actions" class="c-page-toolbar__header">
            <component :is="titleTag" v-if="title" :id="titleId" class="c-page-toolbar__title">{{ title }}</component>
            <div v-if="$slots.actions" class="c-page-toolbar__actions">
                <slot name="actions"></slot>
            </div>
        </div>
        <slot></slot>
    </section>
</template>

<script>
let uid = 0;

/**
 * The upper box of a page (after the design system's `c-page-toolbar`): a card, at most 1440px
 * wide and centred, with a header row — the `title` (rendered as `titleTag`, default `h1`; the
 * toolbar is labelled by it) and the `actions` slot right-aligned beside it (typically
 * `.btn.c-icon-button` buttons) — and below it the default slot, typically a `FilterBar`. The
 * header is only rendered with a title or actions. Pure layout: it loads nothing and holds no
 * state, so any page can use it — a card directory (`CardDirectory` renders one) as well as a
 * module page with a view of its own below it (e.g. a file list).
 *
 * @since 1.20
 */
export default {
    name: 'PageToolbar',
    props: {
        title: { type: String, default: '' },
        titleTag: { type: String, default: 'h1' },
    },
    data() {
        return {
            titleId: `page-toolbar-title-${++uid}`,
        };
    },
};
</script>
