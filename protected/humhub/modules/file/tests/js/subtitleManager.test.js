import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import SubtitleManager from '../../vue/SubtitleManager.vue';
import { LANGUAGE_CODES } from '../../vue/languages.js';

await import('../../../../resources/js/humhub/humhub.url.js');
const vueModule = await import('../../../../resources/js/humhub/humhub.vue.js');

const variant = (language, over = {}) => ({
    name: `subtitles-${language}.vtt`,
    type: 'subtitles',
    language,
    label: language === 'de' ? 'Deutsch' : 'English',
    kind: 'subtitles',
    generated: false,
    size: 40,
    updatedAt: '2026-11-27T10:00:00+00:00',
    url: `/file/file/download?guid=g&variant=subtitles-${language}.vtt`,
    ...over,
});

const stubs = () => globalThis.humhubStubs;
let announce;
let lastPut;

const manager = () => mount(SubtitleManager, {
    props: { fileId: 7, fileName: 'talk.mp4' },
    attachTo: document.body,
});

const rejection422 = (errors) => Promise.reject({ status: 422, errors });

describe('SubtitleManager', () => {
    let variants;

    beforeEach(() => {
        document.documentElement.lang = 'de';
        variants = [variant('de'), variant('en', { kind: 'captions', generated: true })];
        stubs().client.get = vi.fn(() => Promise.resolve({ results: [...variants, { name: 'old-1', type: 'version' }] }));
        lastPut = null;
        stubs().client.ajax = vi.fn((url, cfg) => {
            lastPut = { url, cfg };
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
    });

    it('lists the languages with kind and the automatic badge', async () => {
        const wrapper = manager();
        await flushPromises();

        expect(stubs().client.get.mock.calls[0][0]).toContain('file/7/variants');
        const rows = wrapper.findAll('.subtitle-manager-item');
        expect(rows).toHaveLength(2);
        expect(rows[0].text()).toContain('Deutsch');
        expect(rows[0].text()).toContain('Subtitles');
        expect(rows[0].find('.badge').exists()).toBe(false);
        expect(rows[1].text()).toContain('Captions');
        expect(rows[1].find('.badge').text()).toBe('automatic');
        expect(rows[0].find('a.subtitle-manager-download').attributes('href')).toContain('variant=subtitles-de.vtt');
    });

    it('shows an empty state', async () => {
        variants = [];
        const wrapper = manager();
        await flushPromises();

        expect(wrapper.find('.subtitle-manager-empty').text()).toBe('No subtitles yet.');
    });

    it('offers the UI language first and leaves out the languages that exist', async () => {
        variants = [variant('en')];
        const wrapper = manager();
        await flushPromises();
        await wrapper.find('.subtitle-manager-add').trigger('click');

        const values = wrapper.findAll('select#subtitle-7-language option').map((option) => option.element.value);
        expect(values[0]).toBe('de');
        expect(values).not.toContain('en');
        expect(values).toHaveLength(LANGUAGE_CODES.length - 1);
        expect(wrapper.find('select#subtitle-7-language option').text()).toBe('Deutsch');
    });

    it('adds a language with a PUT of content and kind', async () => {
        variants = [variant('de')];
        const wrapper = manager();
        await flushPromises();
        await wrapper.find('.subtitle-manager-add').trigger('click');

        // The UI language exists, so the first option is the next one by name
        await wrapper.find('select#subtitle-7-language').setValue('fr');
        await wrapper.find('select#subtitle-7-kind').setValue('captions');
        await wrapper.find('textarea').setValue('WEBVTT\n\n00:00.000 --> 00:01.000\nHi\n');
        await wrapper.find('form').trigger('submit');
        await flushPromises();

        expect(lastPut.url).toContain('file/7/variants/subtitles-fr.vtt');
        expect(lastPut.cfg.method).toBe('PUT');
        expect(JSON.parse(lastPut.cfg.data)).toEqual({ content: 'WEBVTT\n\n00:00.000 --> 00:01.000\nHi\n', kind: 'captions' });
        expect(wrapper.find('form').exists()).toBe(false);
        expect(stubs().client.get).toHaveBeenCalledTimes(2);
        expect(announce).toHaveBeenCalledWith('Subtitles saved: Französisch');
    });

    it('edits a variant: loads its text, the language is fixed', async () => {
        const wrapper = manager();
        await flushPromises();
        await wrapper.find('.subtitle-manager-edit').trigger('click');
        await flushPromises();

        expect(fetch).toHaveBeenCalledWith(variants[0].url, { credentials: 'same-origin' });
        expect(wrapper.find('textarea').element.value).toBe('WEBVTT\n\n1\n');
        expect(wrapper.find('select#subtitle-7-language').exists()).toBe(false);
        expect(wrapper.find('.subtitle-manager-language').text()).toBe('Deutsch');

        await wrapper.find('form').trigger('submit');
        await flushPromises();

        expect(lastPut.url).toContain('file/7/variants/subtitles-de.vtt');
    });

    it('deletes after a confirmation only', async () => {
        stubs().client.ajax = vi.fn(() => {
            variants = [variants[1]];
            return Promise.resolve();
        });
        stubs().modal.confirm = vi.fn(() => Promise.resolve(false));
        const wrapper = manager();
        await flushPromises();

        await wrapper.find('.subtitle-manager-delete').trigger('click');
        await flushPromises();
        expect(stubs().client.ajax).not.toHaveBeenCalled();

        stubs().modal.confirm = vi.fn(() => Promise.resolve(true));
        await wrapper.find('.subtitle-manager-delete').trigger('click');
        await flushPromises();

        expect(stubs().modal.confirm.mock.calls[0][0].body).toContain('Deutsch');
        expect(stubs().client.ajax.mock.calls[0][0]).toContain('file/7/variants/subtitles-de.vtt');
        expect(stubs().client.ajax.mock.calls[0][1].method).toBe('DELETE');
        expect(wrapper.findAll('.subtitle-manager-item')).toHaveLength(1);
        expect(announce).toHaveBeenCalledWith('Subtitles deleted: Deutsch');
        // The neighbouring entry's Edit button takes the focus
        expect(document.activeElement.dataset.name).toBe('subtitles-en.vtt');
    });

    it('shows the server errors under the field', async () => {
        stubs().client.ajax = vi.fn(() => rejection422({ content: ['Line 3: Invalid cue timing.'] }));
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
        expect(document.activeElement.dataset.name).toBe('subtitles-en.vtt');

        await wrapper.find('.subtitle-manager-add').trigger('click');
        expect(document.activeElement).toBe(wrapper.find('select#subtitle-7-language').element);
    });

    describe('busy states and errors', () => {
        const removeButton = (wrapper) => wrapper.find('.subtitle-manager-delete');

        it('uses aria-disabled and aria-busy and ignores clicks while a save runs', async () => {
            let finish;
            stubs().client.ajax = vi.fn(() => new Promise((resolve) => { finish = resolve; }));
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
            expect(stubs().client.ajax).toHaveBeenCalledTimes(1);
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
            stubs().client.ajax = vi.fn(() => Promise.reject({ status: 500 }));
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
            stubs().client.ajax = vi.fn(() => Promise.reject({ status: 500 }));
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

            stubs().client.ajax = vi.fn(() => Promise.reject({ status: 500 }));
            await removeButton(wrapper).trigger('click');
            await flushPromises();
            expect(seen).toEqual([]);

            stubs().client.ajax = vi.fn(() => Promise.resolve({}));
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
});
