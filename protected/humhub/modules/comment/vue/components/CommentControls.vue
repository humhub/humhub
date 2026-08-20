<template>
    <div class="comment-entry-loader float-end"></div>
    <DropdownMenu
        :toggle-aria-label="toggleMenuLabel"
        menu-id="comment.controls"
        :entries="entries"
        :context="{ comment }"
    >
        <li>
            <!--
                Plain anchor reusing the exact legacy attributes
                (data-action-click="content.permalink" + the two
                data-content-permalink* values) instead of a
                Vue-owned click handler: humhub.action.js binds the
                [data-action-click] delegate on `document` itself
                (see bindAction(document, 'click', ...) in
                humhub.action.js), so it already fires for anchors
                injected anywhere in the DOM, Vue-rendered islands
                included, with zero extra wiring. The `content`
                module (ui.content) is always loaded page-wide
                wherever comments can appear, so this "just works".
                Not a `menu-id`/`entries` entry: that descriptor shape has
                no room for these legacy data attributes - see
                DropdownMenu.vue's own "Slot contract" docblock note.
            -->
            <a
                href="#"
                class="dropdown-item"
                data-action-click="content.permalink"
                :data-content-permalink="permalink"
                :data-content-permalink-title="permalinkTitle"
            >{{ permalinkLabel }}</a>
        </li>
    </DropdownMenu>
</template>

<script>
/**
 * Comment entry dropdown menu. Markup mirrors
 * comment/widgets/views/commentControls.php so existing theme CSS applies
 * unchanged - now composed from the shared, core `DropdownMenu` component
 * (protected/humhub/vue/DropdownMenu.vue, resolved through the global Vue
 * component registry, same as `RichTextOutput`/`LegacyFormWrapper`) rather
 * than repeating the nav/dropdown markup locally. `DropdownMenu`'s
 * `alignEnd`/`toggleClass` defaults already match what this component used
 * to hardcode (`dropdown-menu-end`, `nav-link dropdown-toggle`), so neither
 * prop needs to be passed explicitly here.
 *
 * Edit/delete/admin-delete are purely emitted upward - CommentEntry owns the
 * actual mutation handling (edit mode, modal confirm, delete request, ...).
 * Permalink is fully functional here already - see the template comment
 * above.
 *
 * ## `comment.controls` menu entries (public extension API)
 *
 * Edit/delete render through `DropdownMenu`'s data-driven `menu-id`/`entries` mode (see its
 * own docblock, "Data-driven mode") instead of a hand-rolled `v-if` `<li>` each - this is what
 * lets another module inject/replace/remove a `comment.controls` item via
 * `registerMenuEntry()`/`removeMenuEntry()` (see docs/develop/ui-js-vuejs-extensions.md,
 * "Menu entries"), the same way it could addEntry()/removeEntry() the equivalent legacy
 * `CommentControls::EVENT_INIT` PHP widget-stack menu before comments became an island (see
 * `docs/develop/module-migrate.md`, Unreleased, for that migration). `comment` is passed as
 * this menu's `context` (the full serialized comment, not the discrete props below) so a
 * registered entry's `condition`/`onClick`/`component` can read anything about the comment,
 * including its own namespaced `comment.extensions` entry - see CommentJsonService's
 * EVENT_SERIALIZE_COMMENTS.
 *
 * Built-in entry ids (stable, public - a module targets these with `registerMenuEntry()` to
 * override, or `removeMenuEntry('comment.controls', id)` to remove):
 *  - `edit` - shown while `canEdit`.
 *  - `delete` - shown while `canDelete`; emits `admin-delete` instead of `delete` on click
 *    when `canAdminDelete` is also set. One entry covers both - the server never reports
 *    `canAdminDelete` without `canDelete` (admin-delete is "delete someone else's comment as
 *    a moderator", not a separate action - see `CommentJsonService::serialize()`), so there is
 *    only ever one Delete item to show, exactly as before this migration.
 *
 * The permalink item is deliberately NOT one of these - see the template comment above.
 *
 * `comment.links` (on `CommentEntry.vue`, not here) remains a plain `ExtensionSlot` - a menu
 * entry and a slot component solve different problems: pick a menu entry for another item in
 * *this* dropdown, a slot for a free-form UI fragment elsewhere in the entry (see
 * docs/develop/ui-js-vuejs-extensions.md, "Menu entries vs. extension slots").
 */
import { i18n } from '@humhub/vue';

export default {
    props: {
        // Full serialized comment (see CommentJsonService::serialize()) - added purely so
        // this menu's `context` can expose it; the core entries below keep reading their
        // own discrete props unchanged, to avoid churning them.
        comment: { type: Object, required: true },
        permalink: { type: String, required: true },
        canEdit: { type: Boolean, default: false },
        canDelete: { type: Boolean, default: false },
        canAdminDelete: { type: Boolean, default: false },
    },
    emits: ['edit', 'delete', 'admin-delete'],
    computed: {
        toggleMenuLabel() {
            return i18n.t('base', 'Toggle comment menu');
        },
        permalinkLabel() {
            return i18n.t('CommentModule.base', 'Permalink');
        },
        permalinkTitle() {
            return i18n.t('CommentModule.base', '<strong>Permalink</strong> to this comment');
        },
        // This menu's built-in entries (see the class docblock, "comment.controls menu
        // entries") - `condition`/`onClick` ignore the `context` argument DropdownMenu passes
        // them since this component already has `this.canEdit`/`this.onEdit` etc. directly;
        // only a module's own registered entry needs to read `context.comment`.
        entries() {
            return [
                {
                    id: 'edit',
                    label: i18n.t('CommentModule.base', 'Edit'),
                    condition: () => this.canEdit,
                    onClick: () => this.onEdit(),
                },
                {
                    id: 'delete',
                    label: i18n.t('CommentModule.base', 'Delete'),
                    condition: () => this.canDelete,
                    onClick: () => this.onDelete(),
                },
            ];
        },
    },
    methods: {
        onEdit() {
            this.$emit('edit');
        },
        onDelete() {
            this.$emit(this.canAdminDelete ? 'admin-delete' : 'delete');
        },
    },
};
</script>
