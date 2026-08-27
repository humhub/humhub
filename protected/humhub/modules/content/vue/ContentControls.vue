<template>
    <DropdownMenu
        :toggle-aria-label="resolvedToggleLabel"
        :toggle-class="toggleClass"
        :root-class="rootClass"
        :align-end="alignEnd"
        menu-id="content.controls"
        :entries="mergedEntries"
        :context="menuContext"
        :loading="loading"
        @open="load"
    >
        <template #toggle><slot name="toggle"></slot></template>
        <slot :capabilities="capabilities"></slot>
    </DropdownMenu>
</template>

<script>
/**
 * The context menu of a content record — the island counterpart of
 * `humhub\modules\content\widgets\WallEntryControls`, and the answer to the one problem
 * every module migrating a content list into Vue runs into: that menu is not the module's
 * own. It is the platform's, and modules have extended it for years by adding widget entries
 * in a `WallEntryControls::EVENT_INIT` handler.
 *
 * ## Three sources, one menu
 *
 * 1. **The host's native entries** (`entries` prop) — what the surrounding island renders
 *    itself, in Vue, with real click handlers. A file browser's Download/Rename/Move live
 *    here. Gate them on `capabilities` via each entry's `condition`.
 * 2. **Server-described entries** — the resolved `WallEntryControls` stack of this record,
 *    fetched on menu open from `GET /api/v2/content/<id>/controls`. Every module that
 *    contributes a describable entry keeps working with NO change
 *    (`humhub\modules\ui\menu\DescribableWidget`); one that contributes markup only is
 *    shipped as raw `html` and deprecated.
 * 3. **The client registry** — `registerMenuEntry('content.controls', …)`, for modules that
 *    have moved to the Vue side. These can override (same `id`) or remove entries from
 *    either of the other two sources, since `DropdownMenu` resolves the registry last.
 *
 * ## Lazy by construction
 *
 * The `⋮` toggle renders with no request at all; only opening the menu fetches. That matters
 * because the payload is entirely caller-dependent (`canEdit`, `canDelete`, which modules
 * contribute what) and so cannot live in a cacheable list payload — the same split the
 * comment island makes with `GET comment/<id>/permissions`. A list of 50 rows therefore
 * costs zero requests until someone actually opens a menu, and one per menu after that; the
 * result is kept for the lifetime of the component.
 *
 * ```html
 * <ContentControls
 *     :content-id="item.contentId"
 *     view-context="browser"
 *     :entries="ownEntries"
 *     :context="{ item }"
 * />
 * ```
 *
 * @since 1.20
 */
import { apiUrl, client, i18n, log } from '@humhub/vue';

export default {
    i18nCategories: ['base'],
    props: {
        /** The content id whose menu this is — NOT the owning record's own primary key. */
        contentId: { type: Number, required: true },
        /**
         * Where this menu is being shown, one of the server's `StreamEntryOptions`
         * `VIEW_CONTEXT_*` values (`stream`, `detail`, `modal`, …). Selects the render-options
         * profile the server would have used in the same place, so a menu inside a module's
         * own UI is not offered stream-only actions.
         */
        viewContext: { type: String, default: null },
        /** The host island's own entries, in `DropdownMenu`'s entry shape. */
        entries: { type: Array, default: () => [] },
        /** Merged into what every entry's `condition`/`onClick`/`component` receives. */
        context: { type: Object, default: () => ({}) },
        /**
         * Core entries this host renders ITSELF and does not want from the server, by name
         * (`edit`, `delete`, `permalink`, `pin`, `move`, `archive`, `topics`, `visibility`,
         * `notifications`). Without this a host with its own Edit action gets a second,
         * server-rendered one next to it.
         */
        suppress: { type: Array, default: () => [] },
        toggleAriaLabel: { type: String, default: null },
        toggleClass: { type: String, default: 'nav-link dropdown-toggle' },
        /**
         * Passed straight to `DropdownMenu`. The default is the platform's corner-controls
         * markup, which is positioned `absolute` platform-wide — a host rendering this menu
         * inline (a list row, a toolbar) has to say so. See `DropdownMenu`'s own docblock.
         */
        rootClass: { type: String, default: 'nav nav-pills preferences' },
        alignEnd: { type: Boolean, default: true },
    },
    // Fires once the server's answer is in, so a host that wants to react to the caller's
    // permissions (rather than only gate menu entries on them) does not have to ask again.
    emits: ['loaded'],
    data() {
        return {
            loading: false,
            loaded: false,
            serverEntries: [],
            capabilities: {},
        };
    },
    computed: {
        resolvedToggleLabel() {
            return this.toggleAriaLabel || i18n.t('base', 'Toggle stream entry menu');
        },
        /**
         * Host entries first, then the server's — minus any the host already renders itself.
         * A host that provides its own `delete` entry means it, and the server's version of
         * the same id would otherwise appear twice.
         */
        mergedEntries() {
            const ownIds = this.entries.map((entry) => entry.id);

            return this.entries.concat(
                this.serverEntries.filter((entry) => ownIds.indexOf(entry.id) === -1),
            );
        },
        menuContext() {
            return { ...this.context, contentId: this.contentId, capabilities: this.capabilities };
        },
    },
    methods: {
        load() {
            if (this.loaded || this.loading) {
                return;
            }

            this.loading = true;

            const params = {};
            if (this.viewContext) {
                params.viewContext = this.viewContext;
            }
            if (this.suppress.length) {
                params.suppress = this.suppress.join(',');
            }

            client.get(apiUrl('content/' + this.contentId + '/controls', params)).then((response) => {
                this.serverEntries = response.entries || [];
                this.capabilities = response.capabilities || {};
                this.loaded = true;
                this.loading = false;
                this.$emit('loaded', this.capabilities);
            }).catch((e) => {
                this.loading = false;
                // Deliberately NOT marked loaded: the host's own entries stay usable, and
                // reopening the menu retries instead of leaving it permanently short.
                log.error(e, true);
            });
        },
    },
};
</script>
