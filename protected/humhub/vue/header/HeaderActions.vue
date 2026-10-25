<template>
    <div :class="rootClass">
        <a
            v-for="action in groups.tools"
            :key="action.id"
            v-bind="action.htmlOptions || {}"
            :class="['btn', `btn-${toolVariant}`, 'btn-icon-only']"
            :href="action.url"
            :aria-label="action.label"
            :title="action.label"
            :data-action-id="action.id"
            @click="onAction($event, action)"
        ><i :class="['ti', `ti-${action.icon}`]" aria-hidden="true"></i></a>
        <slot></slot>
        <a
            v-if="groups.primary"
            v-bind="groups.primary.htmlOptions || {}"
            :class="['btn', 'btn-accent', 'btn-icon-only']"
            :href="groups.primary.url"
            :aria-label="groups.primary.label"
            :title="groups.primary.label"
            :data-action-id="groups.primary.id"
            @click="onAction($event, groups.primary)"
        ><i :class="['ti', `ti-${groups.primary.icon}`]" aria-hidden="true"></i></a>
        <DropdownMenu
            v-if="menu.length"
            :menu-id="menuId"
            :entries="menuEntries"
            :toggle-aria-label="moreLabel"
            :toggle-title="moreLabel"
            toggle-class="btn btn-ghost btn-icon-only"
            :root-class="`${rootClass}-menu list-unstyled m-0`"
        >
            <template #toggle><i class="ti ti-dots-vertical" aria-hidden="true"></i></template>
        </DropdownMenu>
        <slot name="end"></slot>
    </div>
</template>

<script>
import { i18n, modal } from '@humhub/vue';
import DropdownMenu from '../DropdownMenu.vue';

let uid = 0;

/**
 * The actions of a header (`PageHeader`, `ContentCard`), whatever their sort order: the tools, the
 * default slot (stateful tools of the page's own), the primary action (accent), then the ⋮
 * `DropdownMenu` and the `end` slot (what takes the ⋮'s place, e.g. a table's selection menu). A second primary action is shown as a tool. An internal part of those two
 * components; the PHP counterparts are `humhub\widgets\menu\HeaderActions` and a header's ⋮
 * menu.
 *
 * - `rootClass` prop: the class of the actions (`page-header-actions`, `card-header-actions`);
 *   the ⋮ menu gets it with `-menu`.
 * - `toolVariant` prop: the button variant of the tools (`secondary`, `ghost`).
 * - `actions` prop: `[{ id, icon, label, url, modal?, primary?, htmlOptions? }]`, typically
 *   `humhub\widgets\menu\Menu::getEntriesData()`. `modal: true` opens `url` in the global
 *   modal instead of following the link; `htmlOptions` are extra attributes of the link.
 * - `menu` prop: the entries of the ⋮ menu, in the same shape (a `DropdownMenu`'s
 *   `getEntriesData()`); the ⋮ button is only shown with entries.
 *
 * @since 1.20
 */
export default {
    name: 'HeaderActions',
    components: { DropdownMenu },
    props: {
        actions: { type: Array, default: () => [] },
        menu: { type: Array, default: () => [] },
        rootClass: { type: String, required: true },
        toolVariant: { type: String, default: 'secondary' },
    },
    data() {
        return {
            menuId: `header-menu-${++uid}`,
        };
    },
    computed: {
        groups() {
            const groups = { tools: [], primary: null };
            for (const action of this.actions) {
                if (action.primary && !groups.primary) {
                    groups.primary = action;
                } else {
                    groups.tools.push(action);
                }
            }
            return groups;
        },
        menuEntries() {
            return this.menu.map((action) => ({
                id: action.id,
                icon: action.icon,
                label: action.label,
                url: action.url,
                htmlOptions: action.htmlOptions || {},
                onClick: action.modal ? () => modal.load(action.url) : undefined,
            }));
        },
        moreLabel() {
            return i18n.t('base', 'More actions');
        },
    },
    methods: {
        /**
         * Opens a `modal: true` action in the global modal; any other action is a plain link.
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
