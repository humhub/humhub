<template>
    <component :is="title ? 'section' : 'div'" class="card content-card" :aria-labelledby="title ? titleId : null">
        <div v-if="hasHeader" class="card-header">
            <div v-if="title" class="card-heading">
                <component :is="titleTag" :id="titleId" class="card-title">{{ title }}</component>
                <p v-if="description" class="card-subtitle">{{ description }}</p>
            </div>
            <slot name="header"></slot>
            <HeaderActions
                v-if="actions.length || menu.length || $slots.actions"
                :actions="actions"
                :menu="menu"
                root-class="card-header-actions"
                tool-variant="ghost"
            >
                <slot name="actions"></slot>
            </HeaderActions>
        </div>
        <div class="card-body">
            <slot></slot>
        </div>
        <div v-if="$slots.footer" class="card-footer">
            <slot name="footer"></slot>
        </div>
    </component>
</template>

<script>
import HeaderActions from './header/HeaderActions.vue';

let uid = 0;

/**
 * The content card below a page header (`<content-card>`, Bootstrap's `.card`, see
 * `docs/ui/card.md`): an optional header, the body (the default slot) and an optional footer
 * (`footer` slot). The PHP widget `humhub\widgets\ContentCard` renders the same markup.
 *
 * - `title` prop: the card's title (rendered as `titleTag`, default `h2`); the card is then a
 *   `<section>` labelled by it. `description`: a short line below the title.
 * - `header` slot: further header content, e.g. the content navigation (‹ ›, a path bar) of a
 *   card without a title.
 * - `actions` and `menu` props, `actions` slot: as for `PageHeader`, but with ghost tools.
 *
 * The header is only rendered with a title, header content or actions.
 *
 * @since 1.20
 */
export default {
    name: 'ContentCard',
    components: { HeaderActions },
    props: {
        title: { type: String, default: '' },
        titleTag: { type: String, default: 'h2' },
        description: { type: String, default: '' },
        actions: { type: Array, default: () => [] },
        menu: { type: Array, default: () => [] },
    },
    data() {
        return {
            titleId: `card-title-${++uid}`,
        };
    },
    computed: {
        hasHeader() {
            return !!(this.title || this.actions.length || this.menu.length || this.$slots.header || this.$slots.actions);
        },
    },
};
</script>
