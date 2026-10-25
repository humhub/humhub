<template>
    <section class="page-header" :aria-labelledby="title ? titleId : null">
        <div v-if="title || actions.length || menu.length || $slots.actions" class="page-header-bar">
            <a
                v-if="backUrl"
                :href="backUrl"
                class="page-header-back btn btn-ghost btn-icon-only"
                :aria-label="backLabel"
                :title="backLabel"
            ><i class="ti ti-arrow-left" aria-hidden="true"></i></a>
            <component :is="titleTag" v-if="title" :id="titleId" class="page-header-title">{{ title }}</component>
            <HeaderActions
                v-if="actions.length || menu.length || $slots.actions"
                :actions="actions"
                :menu="menu"
                root-class="page-header-actions"
            >
                <slot name="actions"></slot>
            </HeaderActions>
        </div>
        <div v-if="$slots.default" class="page-header-body">
            <slot></slot>
        </div>
    </section>
</template>

<script>
import { i18n } from '@humhub/vue';
import HeaderActions from './header/HeaderActions.vue';

let uid = 0;

/**
 * The card at the top of a page (`.page-header`, see `docs/ui/page-header.md`): a bar with the
 * `title` (rendered as `titleTag`, default `h1`; the header is labelled by it), the optional
 * back arrow (`backUrl`, shown while a surrounding master/detail layout is folded) and the
 * actions, and below it the body (the default slot, typically a `FilterBar`). The bar is only
 * rendered with a title or actions. Pure layout: it loads nothing and holds no state. The PHP
 * widget `humhub\widgets\PageHeader` renders the same markup.
 *
 * - `actions` prop: the actions as data, typically a `PageHeaderActions` menu's entries
 *   (`humhub\widgets\menu\Menu::getEntriesData()`), secondary tools and the primary one last.
 * - `menu` prop: the entries of the ⋮ menu after the actions, in the same shape.
 * - `actions` slot: stateful tools of the page's own (e.g. a view switch); they come before
 *   the primary action.
 *
 * @since 1.20
 */
export default {
    name: 'PageHeader',
    components: { HeaderActions },
    props: {
        title: { type: String, default: '' },
        titleTag: { type: String, default: 'h1' },
        actions: { type: Array, default: () => [] },
        menu: { type: Array, default: () => [] },
        backUrl: { type: String, default: '' },
    },
    data() {
        return {
            titleId: `page-header-title-${++uid}`,
        };
    },
    computed: {
        backLabel() {
            return i18n.t('base', 'Back');
        },
    },
};
</script>
