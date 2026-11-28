import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import { h } from 'vue';
import SubtitleManager from '../../vue/SubtitleManager.vue';
import PickerInput from '../../../../vue/PickerInput.vue';
import ExtensionSlot from '../../../../vue/ExtensionSlot.vue';

await import('../../../../resources/js/humhub/humhub.url.js');
const vueModule = await import('../../../../resources/js/humhub/humhub.vue.js');

// A subtitle track is a variant by its name: `<kind>-<language>.vtt`
const variant = (language, kind = 'subtitles') => ({
    name: `${kind}-${language}.vtt`,
    persistent: true,
    mimeType: 'text/vtt',
    size: 40,
    updatedAt: '2026-11-27T10:00:00+00:00',
    url: `/file/file/download?guid=g&variant=${kind}-${language}.vtt`,
});

const stubs = () => globalThis.humhubStubs;
// The registry of the core bridge (`register()`, `registerSlotComponent()`)
const humhubVue = () => globalThis.humhub.modules.vue;
let announce;
let lastPost;

// The platform's languages, as the controller passes them (names sorted by the server)
const LANGUAGES = [
    { code: 'de', name: 'Deutsch' },
    { code: 'en-GB', name: 'English (UK)' },
    { code: 'en-US', name: 'English (US)' },
    { code: 'fr', name: 'Français' },
    { code: 'pt-BR', name: 'Português do Brasil' },
];

// An extension of the `file.subtitles` slot: shows its context, its button calls `reload()`
const TestSubtitlesExtension = {
    name: 'TestSubtitlesExtension',
    props: { fileId: Number, fileName: String, variants: Array, reload: Function },
    render() {
        return h('button', {
            type: 'button',
            class: 'test-subtitles-extension',
            onClick: () => this.reload(),
        }, `${this.fileId} ${this.fileName} ${this.variants.map((variant) => variant.name).join(',')}`);
    },
};

// `global.components` stands in for the registry the core `PickerInput` and `ExtensionSlot` resolve through.
const manager = (props = {}) => mount(SubtitleManager, {
    props: { fileId: 7, saveUrl: '/file/subtitles/save?guid=g', deleteUrl: '/file/subtitles/delete?guid=g', fileName: 'talk.mp4', languages: LANGUAGES, uiLanguage: 'de', ...props },
    global: { components: { PickerInput, ExtensionSlot, TestSubtitlesExtension } },
    attachTo: document.body,
});

const languageField = (wrapper) => wrapper.findComponent(PickerInput);
const optionTexts = (wrapper) => languageField(wrapper).props('options').map((option) => option.text);

const rejection422 = (errors) => Promise.reject({ status: 422, errors });

describe('SubtitleManager', () => {
    let variants;

    beforeEach(() => {
        document.documentElement.lang = 'de';
        variants = [variant('de'), variant('en', 'captions')];
        stubs().client.get = vi.fn(() => Promise.resolve({ results: [...variants, { name: 'preview-image', persistent: false, url: '/p.jpg' }, { name: 'old-12', persistent: true, url: '/old' }, { name: 'subtitles-de.vtt.bak', persistent: true, url: '/x' }] }));
        lastPost = null;
        stubs().client.post = vi.fn((url, cfg) => {
            lastPost = { url, cfg };
            return Promise.resolve({});
        });
        stubs().modal.confirm = vi.fn(() => Promise.resolve(true));
        announce = vi.spyOn(vueModule.default ?? globalThis.humhub.modules.vue, 'announce').mockImplementation(() => {});
        vi.stubGlobal('fetch', vi.fn(() => Promise.resolve({ ok: true, text: () => Promise.resolve('WEBVTT\n\n1\n') })));
    });

    afterEach(() => {
        document.body.innerHTML = '';
        vi.unstubAllGlobals();
        vi.restoreAllMocks();
        humhubVue().resetSlotRegistry();
    });

    it('lists the languages with kind', async () => {
        const wrapper = manager();
        await flushPromises();

        expect(stubs().client.get.mock.calls[0][0]).toContain('file/7/variants');
        const rows = wrapper.findAll('.subtitle-manager-item');
        expect(rows).toHaveLength(2);
        expect(rows[0].text()).toContain('Deutsch');
        expect(rows[0].text()).toContain('Subtitles');
        expect(rows[0].find('.badge').exists()).toBe(false);
        expect(rows[1].text()).toContain('Captions');
        // Not in the platform's languages: the browser's name for it
        expect(rows[1].text()).toContain('Englisch');
        expect(rows[0].find('a.subtitle-manager-download').attributes('href')).toContain('variant=subtitles-de.vtt');
    });

    it('shows an empty state', async () => {
        variants = [];
        const wrapper = manager();
        await flushPromises();

        expect(wrapper.find('.subtitle-manager-empty').text()).toBe('No subtitles yet.');
    });

    it('offers the languages of the prop, the UI language first and the existing ones left out', async () => {
        variants = [variant('en-US')];
        const wrapper = manager({ uiLanguage: 'fr' });
        await flushPromises();
        await wrapper.find('.subtitle-manager-add').trigger('click');

        expect(languageField(wrapper).props('options')).toEqual([
            { id: 'fr', text: 'Français' },
            { id: 'de', text: 'Deutsch' },
            { id: 'en-GB', text: 'English (UK)' },
            { id: 'pt-BR', text: 'Português do Brasil' },
        ]);
        expect(wrapper.find('select#subtitle-7-language').exists()).toBe(false);
    });

    it('preselects the UI language, else the first language that is left', async () => {
        variants = [];
        const wrapper = manager();
        await flushPromises();
        await wrapper.find('.subtitle-manager-add').trigger('click');
        expect(languageField(wrapper).props('modelValue')).toBe('de');

        document.body.innerHTML = '';
        variants = [variant('de')];
        const other = manager({ uiLanguage: '' });
        await flushPromises();
        await other.find('.subtitle-manager-add').trigger('click');
        expect(languageField(other).props('modelValue')).toBe('en-GB');
        expect(optionTexts(other)).not.toContain('Deutsch');
    });

    it('is a single, searchable choice with a label', async () => {
        const wrapper = manager();
        await flushPromises();
        await wrapper.find('.subtitle-manager-add').trigger('click');

        const picker = languageField(wrapper);
        expect(picker.props('multiple')).toBe(false);
        expect(picker.props('search')).toBe(0);
        expect(picker.props('required')).toBe(true);
        const input = wrapper.find('input#subtitle-7-language');
        expect(input.exists()).toBe(true);
        expect(wrapper.find('label[for="subtitle-7-language"]').text()).toBe('Language');
    });

    it('adds a language with a POST of name and content', async () => {
        variants = [variant('de')];
        const wrapper = manager();
        await flushPromises();
        await wrapper.find('.subtitle-manager-add').trigger('click');

        // The UI language exists, so the first option is the next one
        await languageField(wrapper).vm.$emit('update:modelValue', 'fr');
        await wrapper.find('select#subtitle-7-kind').setValue('captions');
        await wrapper.find('textarea').setValue('WEBVTT\n\n00:00.000 --> 00:01.000\nHi\n');
        await wrapper.find('form').trigger('submit');
        await flushPromises();

        expect(lastPost.url).toBe('/file/subtitles/save?guid=g');
        expect(JSON.parse(lastPost.cfg.data)).toEqual({ name: 'captions-fr.vtt', content: 'WEBVTT\n\n00:00.000 --> 00:01.000\nHi\n' });
        expect(wrapper.find('form').exists()).toBe(false);
        expect(stubs().client.get).toHaveBeenCalledTimes(2);
        expect(announce).toHaveBeenCalledWith('Subtitles saved: Français');
    });

    it('edits a variant: loads its text, the language is fixed', async () => {
        const wrapper = manager();
        await flushPromises();
        await wrapper.find('.subtitle-manager-edit').trigger('click');
        await flushPromises();

        expect(fetch).toHaveBeenCalledWith(variants[0].url, { credentials: 'same-origin' });
        expect(wrapper.find('textarea').element.value).toBe('WEBVTT\n\n1\n');
        expect(wrapper.findComponent(PickerInput).exists()).toBe(false);
        expect(wrapper.find('.subtitle-manager-language').text()).toBe('Deutsch');

        await wrapper.find('form').trigger('submit');
        await flushPromises();

        expect(lastPost.url).toBe('/file/subtitles/save?guid=g');
        expect(JSON.parse(lastPost.cfg.data).name).toBe('subtitles-de.vtt');
    });

    it('changing the kind saves the new name, then deletes the old one, announcing the change once', async () => {
        const changed = vi.fn();
        document.addEventListener('humhub:file:variants-changed', changed);
        const wrapper = manager();
        await flushPromises();
        await wrapper.find('.subtitle-manager-edit').trigger('click');
        await flushPromises();
        await wrapper.find('select#subtitle-7-kind').setValue('captions');
        await wrapper.find('form').trigger('submit');
        await flushPromises();
        document.removeEventListener('humhub:file:variants-changed', changed);

        const calls = stubs().client.post.mock.calls.map(([url, cfg]) => [url, JSON.parse(cfg.data).name]);
        expect(calls).toEqual([['/file/subtitles/save?guid=g', 'captions-de.vtt'], ['/file/subtitles/delete?guid=g', 'subtitles-de.vtt']]);
        expect(changed).toHaveBeenCalledTimes(1);
    });

    it('deletes after a confirmation only', async () => {
        stubs().client.post = vi.fn(() => {
            variants = [variants[1]];
            return Promise.resolve();
        });
        stubs().modal.confirm = vi.fn(() => Promise.resolve(false));
        const wrapper = manager();
        await flushPromises();

        await wrapper.find('.subtitle-manager-delete').trigger('click');
        await flushPromises();
        expect(stubs().client.post).not.toHaveBeenCalled();

        stubs().modal.confirm = vi.fn(() => Promise.resolve(true));
        await wrapper.find('.subtitle-manager-delete').trigger('click');
        await flushPromises();

        expect(stubs().modal.confirm.mock.calls[0][0].body).toContain('Deutsch');
        expect(stubs().client.post.mock.calls[0][0]).toBe('/file/subtitles/delete?guid=g');
        expect(JSON.parse(stubs().client.post.mock.calls[0][1].data)).toEqual({ name: 'subtitles-de.vtt' });
        expect(wrapper.findAll('.subtitle-manager-item')).toHaveLength(1);
        expect(announce).toHaveBeenCalledWith('Subtitles deleted: Deutsch');
        // The neighbouring entry's Edit button takes the focus
        expect(document.activeElement.dataset.name).toBe('captions-en.vtt');
    });

    it('shows the server errors under the field', async () => {
        stubs().client.post = vi.fn(() => rejection422({ content: ['Line 3: Invalid cue timing.'] }));
        const wrapper = manager();
        await flushPromises();
        await wrapper.find('.subtitle-manager-add').trigger('click');
        await wrapper.find('textarea').setValue('nonsense');
        await wrapper.find('form').trigger('submit');
        await flushPromises();

        const textarea = wrapper.find('textarea');
        expect(textarea.classes()).toContain('is-invalid');
        expect(wrapper.find('.invalid-feedback').text()).toBe('Line 3: Invalid cue timing.');
        expect(textarea.attributes('aria-describedby')).toContain('subtitle-7-content-error');
        expect(document.activeElement).toBe(textarea.element);
        expect(announce).not.toHaveBeenCalled();
    });

    it('loads a .srt file into the text field', async () => {
        const wrapper = manager();
        await flushPromises();
        await wrapper.find('.subtitle-manager-add').trigger('click');

        const input = wrapper.find('input[type=file]');
        const file = new File(['1\n00:00:01,000 --> 00:00:02,000\nHi\n'], 'talk.srt', { type: 'application/x-subrip' });
        Object.defineProperty(input.element, 'files', { value: [file], configurable: true });
        await input.trigger('change');
        await vi.waitFor(() => expect(wrapper.find('textarea').element.value).toContain('00:00:01,000 --> 00:00:02,000'));
    });

    it('moves the focus into the form and back to the entry', async () => {
        const wrapper = manager();
        await flushPromises();

        await wrapper.findAll('.subtitle-manager-edit')[1].trigger('click');
        await flushPromises();
        expect(document.activeElement).toBe(wrapper.find('select#subtitle-7-kind').element);

        const cancel = wrapper.findAll('button').find((button) => button.text() === 'Cancel');
        await cancel.trigger('click');
        await flushPromises();
        expect(document.activeElement.dataset.name).toBe('captions-en.vtt');

        await wrapper.find('.subtitle-manager-add').trigger('click');
        expect(document.activeElement).toBe(wrapper.find('input#subtitle-7-language').element);
    });

    describe('busy states and errors', () => {
        const removeButton = (wrapper) => wrapper.find('.subtitle-manager-delete');

        it('uses aria-disabled and aria-busy and ignores clicks while a save runs', async () => {
            let finish;
            stubs().client.post = vi.fn(() => new Promise((resolve) => { finish = resolve; }));
            const wrapper = manager();
            await flushPromises();
            await wrapper.find('.subtitle-manager-add').trigger('click');
            await wrapper.find('textarea').setValue('WEBVTT');
            await wrapper.find('form').trigger('submit');

            const submit = wrapper.find('button[type=submit]');
            submit.element.focus();
            expect(submit.attributes('disabled')).toBeUndefined();
            expect(submit.attributes('aria-disabled')).toBe('true');
            expect(submit.attributes('aria-busy')).toBe('true');
            expect(document.activeElement).toBe(submit.element);

            await wrapper.find('form').trigger('submit');
            expect(stubs().client.post).toHaveBeenCalledTimes(1);
            finish({});
            await flushPromises();
        });

        it('keeps the focus on Edit when the text cannot be loaded', async () => {
            vi.stubGlobal('fetch', vi.fn(() => Promise.resolve({ ok: false, status: 500 })));
            const wrapper = manager();
            await flushPromises();
            const edit = wrapper.find('.subtitle-manager-edit');
            await edit.trigger('click');
            await flushPromises();

            expect(wrapper.find('[role=alert]').text()).toContain('could not be loaded');
            expect(edit.attributes('aria-disabled')).toBeUndefined();
            expect(document.activeElement).toBe(edit.element);
        });

        it('keeps the focus on Save when saving fails without field errors', async () => {
            stubs().client.post = vi.fn(() => Promise.reject({ status: 500 }));
            const wrapper = manager();
            await flushPromises();
            await wrapper.find('.subtitle-manager-add').trigger('click');
            await wrapper.find('textarea').setValue('WEBVTT');
            await wrapper.find('form').trigger('submit');
            await flushPromises();

            expect(wrapper.find('.alert-danger').text()).toContain('could not be saved');
            expect(document.activeElement).toBe(wrapper.find('button[type=submit]').element);
        });

        it('returns the focus to Delete when deleting fails', async () => {
            stubs().client.post = vi.fn(() => Promise.reject({ status: 500 }));
            const wrapper = manager();
            await flushPromises();
            await removeButton(wrapper).trigger('click');
            await flushPromises();

            expect(wrapper.find('[role=alert]').text()).toContain('could not be deleted');
            expect(document.activeElement).toBe(removeButton(wrapper).element);
        });

        it('announces a change of the variants on document after a save and a delete, not after a failure', async () => {
            const seen = [];
            const listener = (event) => seen.push(event.detail.fileId);
            document.addEventListener('humhub:file:variants-changed', listener);
            const wrapper = manager();
            await flushPromises();

            stubs().client.post = vi.fn(() => Promise.reject({ status: 500 }));
            await removeButton(wrapper).trigger('click');
            await flushPromises();
            expect(seen).toEqual([]);

            stubs().client.post = vi.fn(() => Promise.resolve({}));
            await removeButton(wrapper).trigger('click');
            await flushPromises();
            await wrapper.find('.subtitle-manager-add').trigger('click');
            await wrapper.find('textarea').setValue('WEBVTT');
            await wrapper.find('form').trigger('submit');
            await flushPromises();
            document.removeEventListener('humhub:file:variants-changed', listener);

            expect(seen).toEqual([7, 7]);
        });

        it('names the icon buttons with Bootstrap tooltips, not title', async () => {
            const wrapper = manager();
            await flushPromises();

            wrapper.findAll('.subtitle-manager-edit, .subtitle-manager-download, .subtitle-manager-delete').forEach((button) => {
                expect(button.attributes('title')).toBeUndefined();
                expect(button.attributes('data-bs-toggle')).toBe('tooltip');
                expect(button.attributes('data-bs-title')).toBe(button.attributes('aria-label'));
            });
        });
    });

    describe('extension slot file.subtitles', () => {
        beforeEach(() => {
            humhubVue().register('TestSubtitlesExtension', TestSubtitlesExtension);
        });

        it('renders a registered extension above the list with the file and its subtitle tracks', async () => {
            humhubVue().registerSlotComponent('file.subtitles', 'TestSubtitlesExtension');
            const wrapper = manager();
            await flushPromises();

            const extension = wrapper.find('.test-subtitles-extension');
            expect(extension.text()).toBe('7 talk.mp4 subtitles-de.vtt,captions-en.vtt');
            expect(wrapper.element.firstElementChild).toBe(extension.element);
            expect(wrapper.find('.subtitle-manager-list').exists()).toBe(true);
        });

        it('reloads the list on reload() and tells the viewer and the audio player', async () => {
            humhubVue().registerSlotComponent('file.subtitles', 'TestSubtitlesExtension');
            const seen = [];
            const listener = (event) => seen.push(event.detail.fileId);
            document.addEventListener('humhub:file:variants-changed', listener);
            const wrapper = manager();
            await flushPromises();

            variants = [variant('de'), variant('en', 'captions'), variant('fr')];
            await wrapper.find('.test-subtitles-extension').trigger('click');
            await flushPromises();
            document.removeEventListener('humhub:file:variants-changed', listener);

            expect(stubs().client.get).toHaveBeenCalledTimes(2);
            expect(seen).toEqual([7]);
            expect(wrapper.findAll('.subtitle-manager-item')).toHaveLength(3);
            expect(wrapper.find('.test-subtitles-extension').text()).toContain('subtitles-fr.vtt');
        });

        it('renders nothing without a registration', async () => {
            const wrapper = manager();
            await flushPromises();

            expect(wrapper.find('.test-subtitles-extension').exists()).toBe(false);
            expect(wrapper.element.firstElementChild.classList.contains('subtitle-manager-list')).toBe(true);
        });
    });
});
