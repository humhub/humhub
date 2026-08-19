<template>
    <div class="comment-entry-loader float-end"></div>
    <ul class="nav nav-pills preferences">
        <li class="nav-item dropdown">
            <a
                href="#"
                class="nav-link dropdown-toggle"
                data-bs-toggle="dropdown"
                :aria-label="toggleMenuLabel"
                aria-haspopup="true"
                aria-expanded="false"
                role="button"
            ></a>

            <ul class="dropdown-menu dropdown-menu-end">
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
                    -->
                    <a
                        href="#"
                        class="dropdown-item"
                        data-action-click="content.permalink"
                        :data-content-permalink="permalink"
                        :data-content-permalink-title="permalinkTitle"
                    >{{ permalinkLabel }}</a>
                </li>
                <li v-if="canEdit">
                    <a href="#" class="dropdown-item" @click.prevent="onEdit">{{ editLabel }}</a>
                </li>
                <li v-if="canDelete">
                    <a href="#" class="dropdown-item" @click.prevent="onDelete">{{ deleteLabel }}</a>
                </li>
            </ul>
        </li>
    </ul>
</template>

<script>
/**
 * Comment entry dropdown menu. Markup mirrors
 * comment/widgets/views/commentControls.php so existing theme CSS applies
 * unchanged.
 *
 * Edit/delete/admin-delete are purely emitted upward - CommentEntry owns the
 * actual mutation handling (edit mode, modal confirm, delete request, ...).
 * Permalink is fully functional here already - see the template comment
 * above.
 */
import { i18n } from '@humhub/vue';

export default {
    props: {
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
        editLabel() {
            return i18n.t('CommentModule.base', 'Edit');
        },
        deleteLabel() {
            return i18n.t('CommentModule.base', 'Delete');
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
