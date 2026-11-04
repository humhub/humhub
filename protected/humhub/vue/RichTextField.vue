<template>
    <div v-additions class="richtext-field" @input="clearOwnError">
        <div
            :id="editorId"
            ref="editor"
            v-editor-options="{
                upload: upload ? uploadOption : null,
                fileHandlers: handlers.length ? () => $refs.handlers : null,
            }"
            class="ProsemirrorEditor"
            :class="layout === 'block' ? 'plainMenu' : 'focusMenu'"
            data-ui-widget="ui.richtext.prosemirror.RichTextEditor"
            data-ui-init
            :data-preset="preset"
            :data-include="JSON.stringify(include)"
            :data-exclude="JSON.stringify(editorExclude)"
            :data-plugin-options="JSON.stringify(pluginOptions)"
            :data-mentioning-url="mentioningUrl"
            :data-placeholder="placeholder"
            :data-focus="autofocus ? 'true' : null"
            data-backup-interval="0"
            :data-backup-cookie-key="backupKey"
        ></div>
        <input
            v-if="upload"
            ref="picker"
            type="file"
            class="d-none"
            multiple
            :accept="pickerAccept"
            @change="onPicked"
            @cancel="onPicked"
        >
        <!-- The file handlers the editor's menu offers (its `fileHandlers` option) -->
        <ul v-if="handlers.length" ref="handlers" class="d-none">
            <FileHandlerItems :handlers="handlers" />
        </ul>
        <textarea
            :id="editorId + '_input'"
            :name="fieldName"
            class="d-none"
            :title="placeholder"
            :aria-describedby="describedBy"
        ></textarea>
        <div v-if="hasError" :id="errorId" class="invalid-feedback d-block">
            <div v-for="(message, index) in errorMessages" :key="index">{{ message }}</div>
        </div>
    </div>
</template>

<script>
/**
 * A `HumHubForm` field holding the platform's richtext editor (ProseMirror, see
 * docs/develop/internals-richtext-editor.md), rendered client-side: no server-rendered form
 * shell. It renders the editor's mount element with the options the PHP widget
 * (`content\widgets\richtext\RichTextField`, which stays the way to put the editor into a
 * server-rendered form) would put on it as data attributes, and boots the same
 * `ui.richtext.prosemirror.RichTextEditor` widget on it through the additions
 * (`v-additions`). Plugins and presets a module registers on the editor
 * (`prosemirror.plugin.registerPlugin()`/`registerPreset()`) therefore work here as in any
 * PHP form.
 *
 * ## Options
 *
 * `preset`, `include`, `exclude`, `pluginOptions` and `mentioningUrl` as on the PHP widget;
 * `layout` `inline` (the menu shows on focus, `focusMenu`) or `block` (always shown,
 * `plainMenu`, without the resize handle - as the PHP widget does); `autofocus`.
 *
 * ## Files in the text
 *
 * `upload` turns on the editor's upload - `true` uploads through the HTTP API
 * (`POST /api/v2/file`, refused files are reported as a status message), a function
 * `(files) => Promise<file[]>` uploads the browser `File`s itself and resolves with the
 * uploaded ones in the API's file shape. Its menu entry opens a file picker, a pasted file is
 * uploaded too, and each file is inserted
 * where the cursor was (an image or a video as such, anything else as a link; the server
 * attaches what the text references when it is saved). Without it the editor's upload
 * plugin is left out.
 *
 * `handlers` - the file handlers (`{label, icon, attributes}`, the core JS config's
 * `file.handlers`), as `UploadField` takes them - adds them to the
 * editor's upload menu, as the PHP widget's editor has them: the platform's "upload this type"
 * entries go through `upload` with that type, a module's entry (a new document, say) runs its
 * own action, and the editor inserts the file the handler announces on `humhub:file:created`.
 * Without them the editor's file handler plugin is left out.
 *
 * ## Draft backup
 *
 * The editor keeps an unsaved text in `sessionStorage`, keyed by its input's id. That id
 * comes from `instanceKey` - pass one that is unique among the fields mounted at once and
 * stable across page loads for the same logical form (see `CommentForm.vue`), or the draft of
 * one form turns up in another. Without a key the field keeps no backup. The backup runs on
 * this field's own timer (`backupInterval` seconds), which stops when the field unmounts -
 * the widget's own would run for the rest of the page.
 *
 * ## API
 *
 * `getValue()` (the markdown), `setValue(markdown)`, `clear()` (also drops the draft),
 * `focus()` (also this field's `HumHubForm.focusFirstError()` entry point) and
 * `getElement()`, the field's root node. Events: `scroll-active`/`scroll-inactive` while a
 * height-limited editor (`pluginOptions.maxHeight`) scrolls.
 *
 * `label`/`hint` (from `form/fieldMixin.js`) are not rendered and `disabled` does not reach
 * the editor; the submit button's busy state guards a request in flight.
 *
 * @since 1.20
 */
import { log } from '@humhub/vue';
import fieldMixin from './form/fieldMixin.js';
import { uploadFiles } from './upload/uploadClient.js';
import FileHandlerItems from './upload/FileHandlerItems.vue';

// Where the widget caches its instance on its node (`RichTextEditor.component`)
const RICHTEXT_COMPONENT_DATA = 'humhub-ui-richtexteditor';
let instanceCounter = 0;

// Hands options a data attribute cannot carry (functions) to the editor widget, which reads
// its options from the node's jQuery data. `created` runs before the node is mounted, so
// before the additions boot the widget on it.
const editorOptions = {
    created(el, binding) {
        Object.entries(binding.value).forEach(([key, value]) => {
            if (value !== null) {
                jQuery(el).data(key, value);
            }
        });
    },
};

export default {
    components: { FileHandlerItems },
    directives: { editorOptions },
    mixins: [fieldMixin],
    props: {
        instanceKey: { type: String, default: null },
        preset: { type: String, default: null },
        include: { type: Array, default: () => [] },
        exclude: { type: Array, default: () => [] },
        pluginOptions: { type: Object, default: () => ({}) },
        mentioningUrl: { type: String, default: null },
        layout: { type: String, default: 'block' },
        autofocus: { type: Boolean, default: false },
        backupInterval: { type: Number, default: 3 },
        // `true` or `(files) => Promise<file[]>`, see "Files in the text"
        upload: { type: [Boolean, Function], default: false },
        // File handlers (`{label, icon, attributes}`), see "Files in the text"
        handlers: { type: Array, default: () => [] },
    },
    emits: ['scroll-active', 'scroll-inactive'],
    data() {
        return {
            // The PHP widget's `AbstractRichTextEditor::BACKUP_COOKIE_KEY`
            backupKey: 'RichTextEditor.backup',
            pickerAccept: null,
            editorId: this.instanceKey
                ? 'richtext-' + this.instanceKey.replace(/[^A-Za-z0-9_-]/g, '-')
                : 'richtext-' + (++instanceCounter),
        };
    },
    computed: {
        editorExclude() {
            const exclude = [...this.exclude];
            if (this.layout === 'block') {
                exclude.push('resizeNav');
            }
            if (!this.upload && !exclude.includes('upload')) {
                exclude.push('upload');
            }
            if (!this.handlers.length && !exclude.includes('file_handler')) {
                exclude.push('file_handler');
            }
            return exclude;
        },
    },
    mounted() {
        // The editor triggers these as jQuery events on its node
        jQuery(this.$refs.editor)
            .on('scrollActive.richtextField', () => this.$emit('scroll-active'))
            .on('scrollInactive.richtextField', () => this.$emit('scroll-inactive'));

        if (this.instanceKey && this.backupInterval > 0) {
            this.backupTimer = setInterval(() => {
                const editor = this.getEditorInstance();
                if (editor) {
                    editor.backup();
                }
            }, this.backupInterval * 1000);
        }
    },
    beforeUnmount() {
        clearInterval(this.backupTimer);
        jQuery(this.$refs.editor).off('.richtextField');

        const editor = this.getEditorInstance();
        if (editor && editor.editor && typeof editor.editor.destroy === 'function') {
            editor.editor.destroy();
        }
    },
    methods: {
        getEditorInstance() {
            return this.$refs.editor ? jQuery(this.$refs.editor).data(RICHTEXT_COMPONENT_DATA) : null;
        },
        /** @returns {string} the editor's markdown */
        getValue() {
            const editor = this.getEditorInstance();
            return editor ? editor.editor.serialize() : '';
        },
        /** Replaces the editor's content (e.g. to edit an existing text). */
        setValue(markdown) {
            const editor = this.getEditorInstance();
            if (editor) {
                editor.editor.init(markdown || '');
            }
        },
        /** Empties the editor and drops its draft. */
        clear() {
            const editor = this.getEditorInstance();
            if (editor) {
                editor.$.trigger('clear');
            }
        },
        focus() {
            const editor = this.getEditorInstance();
            if (editor) {
                editor.focus();
            }
        },
        /**
         * The editor's `upload` option: the given files (pasted), or the ones the user picks;
         * resolves with none when the picker is cancelled.
         */
        uploadOption(files, { accept = null } = {}) {
            if (files && files.length) {
                return this.uploadFiles(files);
            }

            return new Promise((resolve) => {
                this.resolvePick = resolve;
                // The attribute has to be on the input before the click opens the dialog
                this.pickerAccept = accept;
                this.$nextTick(() => {
                    this.$refs.picker.value = '';
                    this.$refs.picker.click();
                });
            }).then((picked) => (picked.length ? this.uploadFiles(picked) : []));
        },
        uploadFiles(files) {
            if (typeof this.upload === 'function') {
                return this.upload(files);
            }

            return uploadFiles(files).then((response) => {
                (response.errors || []).forEach((error) => {
                    log.error(error.fileName + ': ' + (error.messages || []).join(' '), true);
                });
                return response.results || [];
            });
        },
        onPicked(event) {
            if (this.resolvePick) {
                this.resolvePick(Array.from(event.target.files || []));
                this.resolvePick = null;
            }
        },
        /** @returns {Element} the field's root node */
        getElement() {
            return this.$el;
        },
    },
};
</script>
