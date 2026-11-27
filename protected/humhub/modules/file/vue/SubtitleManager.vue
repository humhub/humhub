<template>
    <div class="subtitle-manager">
        <div v-if="loading" class="text-center p-2">
            <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
            <span class="visually-hidden" role="status">{{ loadingLabel }}</span>
        </div>

        <p v-else-if="failed" ref="error" tabindex="-1" class="text-danger m-0" role="alert">{{ errorLabel }}</p>

        <form v-else-if="form" class="subtitle-manager-form" novalidate @submit.prevent="save">
            <div v-if="otherErrors.length" class="alert alert-danger" role="alert">
                <div v-for="(message, index) in otherErrors" :key="index">{{ message }}</div>
            </div>

            <div v-if="form.name" class="mb-3">
                <span class="form-label d-block">{{ languageLabel }}</span>
                <span class="subtitle-manager-language">{{ labelOf(form.language) }}</span>
            </div>
            <div v-else class="mb-3 field-language required">
                <label :for="id('language')" class="form-label">{{ languageLabel }}</label>
                <select
                    :id="id('language')"
                    ref="language"
                    v-model="form.language"
                    class="form-select"
                    aria-required="true"
                >
                    <option v-for="option in languageOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
            </div>

            <div class="mb-3 field-kind">
                <label :for="id('kind')" class="form-label">{{ kindLabel }}</label>
                <select :id="id('kind')" ref="kind" v-model="form.kind" class="form-select">
                    <option value="subtitles">{{ subtitlesLabel }}</option>
                    <option value="captions">{{ captionsLabel }}</option>
                </select>
            </div>

            <div class="mb-3 field-content required">
                <label :for="id('content')" class="form-label">{{ contentLabel }}</label>
                <!-- Own markup instead of `TextareaField`: the text is WebVTT, set in a monospace font -->
                <textarea
                    :id="id('content')"
                    ref="content"
                    v-model="form.content"
                    class="form-control font-monospace"
                    :class="{ 'is-invalid': contentErrors.length }"
                    rows="12"
                    spellcheck="false"
                    aria-required="true"
                    :aria-invalid="contentErrors.length ? 'true' : null"
                    :aria-describedby="id('content-hint') + (contentErrors.length ? ' ' + id('content-error') : '')"
                    @input="errors = {}"
                ></textarea>
                <div :id="id('content-hint')" class="form-text text-muted">{{ contentHint }}</div>
                <div v-if="contentErrors.length" :id="id('content-error')" class="invalid-feedback">
                    <div v-for="(message, index) in contentErrors" :key="index">{{ message }}</div>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <button
                    ref="submit"
                    type="submit"
                    class="btn btn-primary"
                    :aria-disabled="busy ? 'true' : null"
                    :aria-busy="busy ? 'true' : null"
                >
                    <span v-if="busy" class="hh-loader text-center">
                        <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                        <span role="status" class="visually-hidden">{{ loadingLabel }}</span>
                    </span>
                    <template v-else>{{ saveLabel }}</template>
                </button>
                <button type="button" class="btn btn-ghost" :aria-disabled="busy ? 'true' : null" @click="cancel">{{ cancelLabel }}</button>
                <button
                    type="button"
                    class="btn btn-ghost ms-auto subtitle-manager-load"
                    :aria-disabled="busy ? 'true' : null"
                    @click="busy || $refs.file.click()"
                >
                    <i class="ti ti-upload" aria-hidden="true"></i> {{ loadFileLabel }}
                </button>
                <input
                    ref="file"
                    type="file"
                    class="d-none"
                    accept=".vtt,.srt,text/vtt"
                    tabindex="-1"
                    aria-hidden="true"
                    @change="loadFile"
                >
            </div>
        </form>

        <template v-else>
            <p v-if="loadError" class="text-danger" role="alert">{{ loadError }}</p>

            <p v-if="!variants.length" class="text-muted subtitle-manager-empty">{{ emptyLabel }}</p>

            <ul v-else class="list-group list-group-flush subtitle-manager-list">
                <li
                    v-for="variant in variants"
                    :key="variant.name"
                    class="list-group-item d-flex align-items-center gap-2 subtitle-manager-item"
                >
                    <div class="flex-grow-1">
                        <span class="subtitle-manager-label">{{ variant.label }}</span>
                        <span class="text-body-secondary"> · {{ kindOf(variant) }}</span>
                        <span v-if="variant.generated" class="badge ms-1">{{ automaticLabel }}</span>
                    </div>
                    <button
                        type="button"
                        class="btn btn-ghost btn-sm btn-icon-only subtitle-manager-edit"
                        :data-name="variant.name"
                        :aria-disabled="busy ? 'true' : null"
                        :aria-label="editLabelOf(variant)"
                        :data-bs-title="editLabelOf(variant)"
                        data-bs-toggle="tooltip"
                        @click="edit(variant)"
                    ><i class="ti ti-edit" aria-hidden="true"></i></button>
                    <a
                        class="btn btn-ghost btn-sm btn-icon-only subtitle-manager-download"
                        :href="variant.url"
                        :download="variant.name"
                        :aria-label="downloadLabelOf(variant)"
                        :data-bs-title="downloadLabelOf(variant)"
                        data-bs-toggle="tooltip"
                    ><i class="ti ti-download" aria-hidden="true"></i></a>
                    <button
                        type="button"
                        class="btn btn-ghost btn-ghost-danger btn-sm btn-icon-only subtitle-manager-delete"
                        :data-name="variant.name"
                        :aria-disabled="busy ? 'true' : null"
                        :aria-label="deleteLabelOf(variant)"
                        :data-bs-title="deleteLabelOf(variant)"
                        data-bs-toggle="tooltip"
                        @click="remove(variant)"
                    ><i class="ti ti-trash" aria-hidden="true"></i></button>
                </li>
            </ul>

            <button
                type="button"
                class="btn btn-primary btn-sm subtitle-manager-add"
                :aria-disabled="busy ? 'true' : null"
                @click="add"
            ><i class="ti ti-plus" aria-hidden="true"></i> {{ addLabel }}</button>
        </template>
    </div>
</template>

<script>
/**
 * The subtitles of an audio or video file: one entry per language with its kind (subtitles, or
 * captions with sound descriptions) and, for a generated one, an "automatic" badge, and the actions
 * Edit, Download and Delete (confirmed). A form adds a language or edits one: the language (all
 * ISO 639-1 languages in the UI language, the UI language first, the languages that exist left
 * out; fixed while editing), the kind and the WebVTT text, which "Load file" fills from a .vtt or
 * .srt file read in the browser (an SRT text is converted by the server on save).
 *
 * Opened by the "Subtitles" file handler's modal (`file\controllers\SubtitlesController`), which mounts
 * `<subtitle-manager file-id="..." file-name="...">` in its body.
 *
 * Endpoints (see `file\controllers\api\FileController`), `<name>` being `subtitles-<language>.vtt`:
 *
 * - `GET api/v2/file/<id>/variants` lists the variants, the `type` `subtitles` ones are shown.
 * - `PUT api/v2/file/<id>/variants/<name>` with the JSON `{content, kind}` saves one; a `422` carries
 *   the messages under `errors.content` (or `errors.name`, shown above the form).
 * - `DELETE api/v2/file/<id>/variants/<name>` removes one.
 * - The text of a variant to edit is its `url`, fetched from the same origin.
 *
 * Focus: into the form when it opens, back to the language's Edit button after save and cancel, to
 * the neighbouring entry (else "Add language") after a delete. Save and delete are announced. While a request
 * runs the buttons are `aria-disabled` (clicks are ignored) and not `disabled`, so the one with the focus keeps it; a
 * failed request leaves the focus on the button that started it (the field after a 422).
 *
 * After a save or delete the manager dispatches `humhub:file:variants-changed` on `document` with
 * `detail: {fileId}`, for the media viewer and the audio player to forget the subtitles they have cached.
 *
 * @since 1.20
 */
import { apiUrl, announce, client, i18n, log, modal } from '@humhub/vue';
import { LANGUAGE_CODES, languageName } from './languages.js';

const escapeHtml = (text) => String(text).replace(/[&<>"']/g, (c) => `&#${c.charCodeAt(0)};`);

export default {
    i18nCategories: ['FileModule.base', 'base'],
    props: {
        /** The id of the platform file. */
        fileId: { type: Number, required: true },
        /** Its name, for the messages. */
        fileName: { type: String, default: '' },
    },
    data() {
        return {
            variants: [],
            loading: true,
            failed: false,
            loadError: '',
            busy: false,
            // The open form: `{name, language, kind, content}`, `name` is empty for a new language
            form: null,
            errors: {},
        };
    },
    computed: {
        uiLanguage() {
            return (document.documentElement.lang || 'en').split('-')[0].toLowerCase();
        },
        /** All languages with their names, the UI language first, the languages that exist left out when adding. */
        languageOptions() {
            const existing = new Set(this.variants.map((variant) => variant.language));
            const locale = document.documentElement.lang || 'en';

            return LANGUAGE_CODES
                .filter((code) => !existing.has(code))
                .map((code) => ({ value: code, label: languageName(code, locale) }))
                .sort((a, b) => (
                    (b.value === this.uiLanguage) - (a.value === this.uiLanguage)
                    || a.label.localeCompare(b.label, locale)
                ));
        },
        contentErrors() {
            return this.errors.content || [];
        },
        /** Messages of the other fields (an unknown name, ...), shown above the form. */
        otherErrors() {
            return Object.keys(this.errors)
                .filter((key) => key !== 'content')
                .flatMap((key) => this.errors[key]);
        },
        loadingLabel: () => i18n.t('base', 'Loading...'),
        errorLabel: () => i18n.t('FileModule.base', 'The subtitles could not be loaded.'),
        emptyLabel: () => i18n.t('FileModule.base', 'No subtitles yet.'),
        addLabel: () => i18n.t('FileModule.base', 'Add language'),
        automaticLabel: () => i18n.t('FileModule.base', 'automatic'),
        subtitlesLabel: () => i18n.t('FileModule.base', 'Subtitles'),
        captionsLabel: () => i18n.t('FileModule.base', 'Captions (with sound descriptions)'),
        languageLabel: () => i18n.t('FileModule.base', 'Language'),
        kindLabel: () => i18n.t('FileModule.base', 'Type'),
        contentLabel: () => i18n.t('FileModule.base', 'WebVTT text'),
        contentHint: () => i18n.t('FileModule.base', 'WebVTT, or SRT, which is converted when saving.'),
        loadFileLabel: () => i18n.t('FileModule.base', 'Load file'),
        saveLabel: () => i18n.t('base', 'Save'),
        cancelLabel: () => i18n.t('base', 'Cancel'),
    },
    mounted() {
        this.load();
    },
    methods: {
        id(name) {
            return `subtitle-${this.fileId}-${name}`;
        },
        endpoint(name) {
            return apiUrl('file/' + this.fileId + '/variants' + (name ? '/' + encodeURIComponent(name) : ''));
        },
        labelOf(code) {
            return languageName(code, document.documentElement.lang || 'en');
        },
        kindOf(variant) {
            return variant.kind === 'captions'
                ? i18n.t('FileModule.base', 'Captions')
                : i18n.t('FileModule.base', 'Subtitles');
        },
        editLabelOf: (variant) => i18n.t('FileModule.base', 'Edit {language}', { language: variant.label }),
        downloadLabelOf: (variant) => i18n.t('FileModule.base', 'Download {language}', { language: variant.label }),
        deleteLabelOf: (variant) => i18n.t('FileModule.base', 'Delete {language}', { language: variant.label }),
        load() {
            return client.get(this.endpoint())
                .then((response) => {
                    const results = Array.isArray(response.results) ? response.results : [];
                    this.variants = results.filter((variant) => variant.type === 'subtitles');
                    this.loading = false;
                })
                .catch((e) => {
                    this.failed = true;
                    this.loading = false;
                    log.error(e);
                });
        },
        /** Moves the focus to an element once the view has rendered it. */
        focusAfterRender(find) {
            return this.$nextTick(() => {
                const target = find();
                if (target) {
                    target.focus();
                }
            });
        },
        focusEdit(name) {
            return this.focusAfterRender(() => Array.from(this.$el.querySelectorAll('.subtitle-manager-edit'))
                .find((button) => button.dataset.name === name)
                || this.$el.querySelector('.subtitle-manager-add')
                || this.$refs.error);
        },
        focusButton(selector, name) {
            return this.focusAfterRender(() => Array.from(this.$el.querySelectorAll(selector))
                .find((button) => button.dataset.name === name));
        },
        /** The list of the subtitles of the file is out of date: the viewer and the audio player ask again. */
        announceChange() {
            document.dispatchEvent(new CustomEvent('humhub:file:variants-changed', { detail: { fileId: this.fileId } }));
        },
        add() {
            if (this.busy) {
                return;
            }

            const first = this.languageOptions[0];
            this.errors = {};
            this.loadError = '';
            this.form = { name: '', language: first ? first.value : '', kind: 'subtitles', content: '' };
            this.focusAfterRender(() => this.$refs.language);
        },
        edit(variant) {
            if (this.busy) {
                return Promise.resolve();
            }

            this.errors = {};
            this.loadError = '';
            this.busy = true;

            // Same origin; the download answers the text itself (`text/vtt`)
            return fetch(variant.url, { credentials: 'same-origin' })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }
                    return response.text();
                })
                .then((content) => {
                    this.form = { name: variant.name, language: variant.language, kind: variant.kind, content };
                    this.busy = false;
                    this.focusAfterRender(() => this.$refs.kind);
                })
                .catch((e) => {
                    this.busy = false;
                    this.loadError = i18n.t('FileModule.base', 'The subtitles of {language} could not be loaded.', {
                        language: variant.label,
                    });
                    log.error(e);
                    this.focusButton('.subtitle-manager-edit', variant.name);
                });
        },
        cancel() {
            if (this.busy) {
                return;
            }

            const name = this.form.name;
            this.form = null;
            this.errors = {};
            this.focusEdit(name);
        },
        loadFile(event) {
            const input = event.target;
            const file = input.files && input.files[0];
            if (!file) {
                return;
            }

            const reader = new FileReader();
            reader.onload = () => {
                this.form.content = String(reader.result);
                this.errors = {};
                input.value = '';
                this.focusAfterRender(() => this.$refs.content);
            };
            reader.onerror = () => {
                input.value = '';
                log.error(reader.error);
            };
            reader.readAsText(file);
        },
        save() {
            if (this.busy || !this.form) {
                return Promise.resolve();
            }

            const { language, kind, content } = this.form;
            const name = this.form.name || `subtitles-${language}.vtt`;
            this.busy = true;
            this.errors = {};

            return client.put(this.endpoint(name), {
                data: JSON.stringify({ content, kind }),
                contentType: 'application/json',
            })
                .then(() => {
                    this.announceChange();
                    return this.load();
                })
                .then(() => {
                    this.busy = false;
                    this.form = null;
                    announce(i18n.t('FileModule.base', 'Subtitles saved: {language}', { language: this.labelOf(language) }));
                    this.focusEdit(name);
                })
                .catch((response) => {
                    this.busy = false;
                    if (response && response.status === 422) {
                        this.errors = this.errorsOf(response);
                        this.focusAfterRender(() => this.$refs.content);
                    } else {
                        this.errors = { save: [i18n.t('FileModule.base', 'The subtitles could not be saved.')] };
                        log.error(response);
                        this.focusAfterRender(() => this.$refs.submit);
                    }
                });
        },
        /** `{field: [messages]}` of a 422, whichever shape the client hands over. */
        errorsOf(response) {
            const errors = response.errors || (response.error && response.error.errors) || {};
            const result = {};
            Object.keys(errors).forEach((key) => {
                result[key] = [].concat(errors[key]);
            });

            return result;
        },
        remove(variant) {
            if (this.busy) {
                return Promise.resolve();
            }

            return modal.confirm({
                body: i18n.t('FileModule.base', 'Do you want to delete the subtitles {language}?', {
                    language: escapeHtml(variant.label),
                }),
            }).then((confirmed) => {
                if (!confirmed) {
                    return null;
                }

                const index = this.variants.indexOf(variant);
                const neighbour = this.variants[index + 1] || this.variants[index - 1];
                this.busy = true;

                return client.del(this.endpoint(variant.name))
                    .then(() => {
                        this.announceChange();
                        return this.load();
                    })
                    .then(() => {
                        this.busy = false;
                        announce(i18n.t('FileModule.base', 'Subtitles deleted: {language}', { language: variant.label }));
                        this.focusEdit(neighbour ? neighbour.name : '');
                    })
                    .catch((response) => {
                        this.busy = false;
                        this.loadError = i18n.t('FileModule.base', 'The subtitles could not be deleted.');
                        log.error(response);
                        this.focusButton('.subtitle-manager-delete', variant.name);
                    });
            });
        },
    },
};
</script>
