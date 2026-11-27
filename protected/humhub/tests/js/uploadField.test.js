import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { h, nextTick } from 'vue';
import { flushPromises, mount } from '@vue/test-utils';
import UploadField from '../../vue/UploadField.vue';
import HumHubForm from '../../vue/HumHubForm.vue';
import FileRow from '../../vue/FileRow.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

// v-additions is registered per island app by the mounter, so a bare mount() of a
// component using it needs a stand-in (same approach as coreInterop.test.js).
const additionsDirective = {
    mounted(el) {
        globalThis.humhubStubs.additions.applyTo(jQuery(el));
    },
    updated(el) {
        globalThis.humhubStubs.additions.applyTo(jQuery(el));
    },
};

// `UploadList` nests the core `FileRow` through the registry, which `global.components` stands in for
const global = { directives: { additions: additionsDirective }, components: { FileRow } };

const apiFile = (overrides = {}) => ({
    id: 7,
    guid: 'guid-7',
    fileName: 'report.pdf',
    mimeType: 'application/pdf',
    size: 2048,
    mimeIcon: 'mime-pdf',
    icon: 'file-type-pdf',
    url: 'https://example.com/file/file/download?guid=guid-7',
    previewUrl: null,
    ...overrides,
});

const uploaded = (id, fileName) => apiFile({ id, guid: `guid-${id}`, fileName, url: `/file/file/download?guid=guid-${id}` });

const browserFile = (name = 'report.pdf') => new File(['content'], name, { type: 'application/pdf' });

/** Every request to the API, held open until the test answers it. */
let requests;

const fileNameOf = (request) => request.cfg.data.getAll('files[]')[0].name;
const requestFor = (name) => requests.find((request) => fileNameOf(request) === name);
const succeed = (name, file) => requestFor(name).resolve({ results: [file], errors: [] });

let wrapper;

/** Mounts the field with its `v-model` fed back, as a parent with `v-model` does. */
const mountField = (props = {}, options = {}) => {
    wrapper = mount(UploadField, {
        props: {
            attribute: 'fileList',
            'onUpdate:modelValue': (files) => wrapper.setProps({ modelValue: files }),
            ...props,
        },
        global,
        ...options,
    });
    return wrapper;
};

/** A form whose busy state follows the field's `busy`, as the comment form's does. */
const FormHost = {
    props: { formBusy: { type: Boolean, default: false } },
    data: () => ({ files: [], uploadBusy: false }),
    render() {
        return h(HumHubForm, { modelName: 'Comment', busy: this.formBusy || this.uploadBusy }, {
            default: () => h(UploadField, {
                attribute: 'fileList',
                modelValue: this.files,
                'onUpdate:modelValue': (files) => {
                    this.files = files;
                },
                onBusy: (busy) => {
                    this.uploadBusy = busy;
                },
            }),
        });
    },
};

const rows = () => wrapper.findAll('.file-row');
const rowOf = (name) => rows().find((row) => row.text().includes(name));
const removeRow = (name) => rowOf(name).find('button.file-row-action').trigger('click');
const lastModel = () => wrapper.emitted('update:modelValue').at(-1)[0];

beforeEach(() => {
    document.body.replaceChildren();
    requests = [];
    globalThis.humhubStubs.client.post = (url, cfg) => {
        const request = { url, cfg, aborted: false };
        const promise = new Promise((resolve, reject) => {
            request.resolve = resolve;
            request.reject = reject;
        });
        promise.abort = () => {
            request.aborted = true;
            request.reject({ status: 0, textStatus: 'abort' });
        };
        requests.push(request);
        return promise;
    };
    vi.spyOn(globalThis.humhub.modules.vue, 'announce').mockImplementation(() => {});
});

afterEach(() => {
    wrapper?.unmount();
    wrapper = undefined;
    vi.restoreAllMocks();
});

describe('UploadField', () => {
    it('renders the legacy upload trigger markup with a hidden multi-file input', () => {
        mountField();

        const trigger = wrapper.find('.fileinput-button');
        expect(trigger.exists()).toBe(true);
        expect(trigger.find('i.ti.ti-cloud-upload').exists()).toBe(true);

        const input = wrapper.find('input[type="file"]');
        expect(input.exists()).toBe(true);
        expect(input.attributes('multiple')).toBeDefined();
        // Hidden, but accessibility checkers inspect it: named like its trigger
        expect(input.attributes('aria-label')).toBe('Upload files');
    });

    it('shows a row for each added file at once and uploads each in its own request', async () => {
        mountField();

        wrapper.vm.addFiles([browserFile('a.pdf'), browserFile('b.pdf')]);
        await nextTick();

        expect(rows()).toHaveLength(2);
        expect(rows().map((row) => row.classes('is-uploading'))).toEqual([true, true]);
        expect(rowOf('a.pdf').text()).toContain('a.pdf');

        expect(requests).toHaveLength(2);
        requests.forEach((request) => {
            expect(request.url).toContain('/api/v2/file');
            expect(request.cfg.processData).toBe(false);
            expect(request.cfg.contentType).toBe(false);
            expect(request.cfg.data.getAll('files[]')).toHaveLength(1);
        });
        expect(requests.map(fileNameOf)).toEqual(['a.pdf', 'b.pdf']);
        expect(wrapper.emitted('update:modelValue')).toBeUndefined();
    });

    it('runs at most 3 uploads at a time and starts the next when one ends', async () => {
        mountField();

        wrapper.vm.addFiles(['1.pdf', '2.pdf', '3.pdf', '4.pdf', '5.pdf'].map(browserFile));
        await nextTick();

        expect(rows()).toHaveLength(5);
        expect(requests.map(fileNameOf)).toEqual(['1.pdf', '2.pdf', '3.pdf']);

        succeed('1.pdf', uploaded(1, '1.pdf'));
        await flushPromises();

        expect(requests.map(fileNameOf)).toEqual(['1.pdf', '2.pdf', '3.pdf', '4.pdf']);
    });

    it('shows the progress of a file in its row', async () => {
        mountField();

        wrapper.vm.addFiles([browserFile('a.pdf')]);
        await nextTick();

        const xhr = requests[0].cfg.xhr();
        xhr.upload.dispatchEvent(Object.assign(new Event('progress'), {
            lengthComputable: true,
            loaded: 25,
            total: 100,
        }));
        await nextTick();

        expect(wrapper.findComponent(FileRow).props('progress')).toBe(25);
        expect(rowOf('a.pdf').find('.file-row-progress').exists()).toBe(true);
    });

    it('renders only the row of the file whose upload progresses', async () => {
        // Spied before the rows exist, as an instance keeps its render
        const rowRenders = vi.spyOn(FileRow, 'render');
        mountField();
        wrapper.vm.addFiles(['a.pdf', 'b.pdf', 'c.pdf', 'd.pdf'].map(browserFile));
        await flushPromises();
        rowRenders.mockClear();

        const xhr = requestFor('b.pdf').cfg.xhr();
        for (const loaded of [10, 20, 20]) {
            xhr.upload.dispatchEvent(Object.assign(new Event('progress'), { lengthComputable: true, loaded, total: 100 }));
            await nextTick();
        }

        expect(rowRenders.mock.calls.map(([row]) => row.file.fileName)).toEqual(['b.pdf', 'b.pdf']);
    });

    it('turns the row of an uploaded file into the file and adds it to the v-model', async () => {
        mountField();

        wrapper.vm.addFiles([browserFile('a.pdf')]);
        await nextTick();
        const row = rows()[0].element;

        succeed('a.pdf', uploaded(1, 'a.pdf'));
        await flushPromises();

        expect(lastModel()).toEqual([uploaded(1, 'a.pdf')]);
        // The same row, so it stays in place and is announced once
        expect(rows()).toHaveLength(1);
        expect(rows()[0].element).toBe(row);
        expect(rows()[0].classes('is-uploading')).toBe(false);
        expect(rows()[0].attributes('data-preview-guid')).toBe('guid-1');
    });

    it('keeps the files of the v-model in the order they were added, also when they finish together', async () => {
        const existing = uploaded(1, 'old.pdf');
        mountField({ modelValue: [existing] });

        wrapper.vm.addFiles([browserFile('a.pdf'), browserFile('b.pdf')]);
        await nextTick();

        // Both finish before the parent passes the v-model back
        succeed('b.pdf', uploaded(3, 'b.pdf'));
        succeed('a.pdf', uploaded(2, 'a.pdf'));
        await flushPromises();

        expect(lastModel()).toEqual([existing, uploaded(2, 'a.pdf'), uploaded(3, 'b.pdf')]);
        expect(rows().map((row) => row.attributes('data-preview-guid'))).toEqual(['guid-1', 'guid-2', 'guid-3']);
    });

    it('keeps the row of a refused file with the reason, and leaves it out of the v-model', async () => {
        mountField();

        wrapper.vm.addFiles([browserFile('a.pdf'), browserFile('huge.zip')]);
        await nextTick();

        succeed('a.pdf', uploaded(1, 'a.pdf'));
        requestFor('huge.zip').resolve({ results: [], errors: [{ fileName: 'huge.zip', messages: ['The file is too big.'] }] });
        await flushPromises();

        expect(lastModel()).toEqual([uploaded(1, 'a.pdf')]);
        const row = rowOf('huge.zip');
        expect(row.classes('is-invalid')).toBe(true);
        expect(row.find('.invalid-feedback').text()).toBe('The file is too big.');
    });

    it('shows the messages of a 422 response in the row', async () => {
        mountField();

        wrapper.vm.addFiles([browserFile('a.pdf')]);
        await nextTick();

        requests[0].reject({ status: 422, errors: { files: ['No file was uploaded.'] } });
        await flushPromises();

        expect(rowOf('a.pdf').classes('is-invalid')).toBe(true);
        expect(rowOf('a.pdf').text()).toContain('No file was uploaded.');
        expect(wrapper.emitted('update:modelValue')).toBeUndefined();
    });

    it('cancels the upload of a row removed while it uploads', async () => {
        mountField();

        wrapper.vm.addFiles(['1.pdf', '2.pdf', '3.pdf', '4.pdf'].map(browserFile));
        await nextTick();

        await removeRow('2.pdf');
        await flushPromises();

        expect(requestFor('2.pdf').aborted).toBe(true);
        expect(rowOf('2.pdf')).toBeUndefined();
        // Its place goes to the file waiting
        expect(requests.map(fileNameOf)).toEqual(['1.pdf', '2.pdf', '3.pdf', '4.pdf']);
        expect(wrapper.emitted('update:modelValue')).toBeUndefined();
    });

    it('never starts the upload of a row removed while it waits', async () => {
        mountField();

        wrapper.vm.addFiles(['1.pdf', '2.pdf', '3.pdf', '4.pdf'].map(browserFile));
        await nextTick();

        await removeRow('4.pdf');
        ['1.pdf', '2.pdf', '3.pdf'].forEach((name, index) => succeed(name, uploaded(index + 1, name)));
        await flushPromises();

        expect(requests.map(fileNameOf)).toEqual(['1.pdf', '2.pdf', '3.pdf']);
        expect(rows()).toHaveLength(3);
    });

    it('drops the row of a refused file when it is removed', async () => {
        mountField();

        wrapper.vm.addFiles([browserFile('huge.zip')]);
        await nextTick();
        requests[0].resolve({ results: [], errors: [{ fileName: 'huge.zip', messages: ['The file is too big.'] }] });
        await flushPromises();

        await removeRow('huge.zip');

        expect(rows()).toHaveLength(0);
        expect(wrapper.emitted('update:modelValue')).toBeUndefined();
    });

    it('removes an uploaded file locally, without deleting it on the server', async () => {
        const keep = uploaded(1, 'keep.pdf');
        mountField({ modelValue: [keep, uploaded(2, 'other.pdf')] });

        await removeRow('other.pdf');

        expect(lastModel()).toEqual([keep]);
        expect(rows()).toHaveLength(1);
        expect(requests).toHaveLength(0);
    });

    it('reports busy while files upload', async () => {
        mountField();

        wrapper.vm.addFiles([browserFile('a.pdf'), browserFile('b.pdf')]);
        await flushPromises();
        expect(wrapper.emitted('busy').map((args) => args[0])).toEqual([true]);

        succeed('a.pdf', uploaded(1, 'a.pdf'));
        await flushPromises();
        expect(wrapper.emitted('busy').map((args) => args[0])).toEqual([true]);

        requestFor('b.pdf').reject({ status: 500 });
        await flushPromises();
        expect(wrapper.emitted('busy').map((args) => args[0])).toEqual([true, false]);
    });

    it('is no longer busy once the last upload is removed', async () => {
        mountField();

        wrapper.vm.addFiles([browserFile('a.pdf')]);
        await flushPromises();
        await removeRow('a.pdf');
        await flushPromises();

        expect(wrapper.emitted('busy').map((args) => args[0])).toEqual([true, false]);
    });

    it('takes files up to the maximum, counting those still uploading but not refused ones, and says the limit once', async () => {
        mountField({ max: 3, modelValue: [uploaded(1, 'old.pdf')] });
        // The harness i18n stub does not expand ICU plurals, so the message is found by its stable prefix
        const maxMessages = () => wrapper.findAll('div.invalid-feedback > div')
            .filter((message) => message.text().startsWith('This upload field only allows a maximum of'));

        wrapper.vm.addFiles([browserFile('a.pdf'), browserFile('huge.zip')]);
        await nextTick();
        requestFor('huge.zip').resolve({ results: [], errors: [{ fileName: 'huge.zip', messages: ['Too big.'] }] });
        await flushPromises();

        // One done, one uploading, one refused: one more fits
        wrapper.vm.addFiles([browserFile('b.pdf'), browserFile('c.pdf'), browserFile('d.pdf')]);
        await nextTick();

        expect(requests.map(fileNameOf)).toEqual(['a.pdf', 'huge.zip', 'b.pdf']);
        expect(rows().map((row) => row.find('.file-row-name').text())).toEqual(['old.pdf', 'a.pdf', 'huge.zip', 'b.pdf']);
        expect(maxMessages()).toHaveLength(1);

        // Cancelling an upload makes room again
        await removeRow('b.pdf');
        wrapper.vm.addFiles([browserFile('e.pdf')]);
        await nextTick();

        expect(requests.map(fileNameOf)).toEqual(['a.pdf', 'huge.zip', 'b.pdf', 'e.pdf']);
        expect(maxMessages()).toHaveLength(0);
    });

    describe('with a size limit', () => {
        const fileConfig = globalThis.humhub.config.module('file');
        let upload;
        // 7 bytes: over the limit of 5 below
        const largeFile = (name) => new File(['content'], name);
        const smallFile = (name) => new File(['x'], name);

        beforeEach(() => {
            upload = fileConfig.upload;
            fileConfig.upload = { ...upload, maxFileSize: 5 };
        });

        afterEach(() => {
            fileConfig.upload = upload;
        });

        it('refuses a file over it at once, in its row and without a request', async () => {
            mountField();

            wrapper.vm.addFiles([smallFile('a.pdf'), largeFile('huge.zip'), smallFile('b.pdf')]);
            await nextTick();

            expect(requests.map(fileNameOf)).toEqual(['a.pdf', 'b.pdf']);
            // In the order they came
            expect(rows().map((row) => row.find('.file-row-name').text())).toEqual(['a.pdf', 'huge.zip', 'b.pdf']);
            const row = rowOf('huge.zip');
            expect(row.classes('is-invalid')).toBe(true);
            expect(row.find('.invalid-feedback').text()).toBe('The file is too large. Maximum: 5 B');
            expect(row.find('[role="progressbar"]').exists()).toBe(false);
            expect(wrapper.emitted('busy')).toEqual([[true]]);

            // Removing it drops the row
            await removeRow('huge.zip');
            expect(rowOf('huge.zip')).toBeUndefined();
            expect(wrapper.emitted('update:modelValue')).toBeUndefined();
        });

        it('leaves the place of a refused file to one that fits the maximum', async () => {
            mountField({ max: 1 });

            wrapper.vm.addFiles([largeFile('huge.zip'), smallFile('a.pdf')]);
            await nextTick();

            expect(requests.map(fileNameOf)).toEqual(['a.pdf']);
            expect(wrapper.findAll('div.invalid-feedback > div')).toHaveLength(0);
        });
    });

    it('clear() cancels the uploads and empties the rows and the v-model', async () => {
        mountField({ modelValue: [uploaded(1, 'old.pdf')] });

        wrapper.vm.addFiles([browserFile('a.pdf'), browserFile('b.pdf')]);
        await nextTick();

        wrapper.vm.clear();
        await flushPromises();

        expect(requests.map((request) => request.aborted)).toEqual([true, true]);
        expect(rows()).toHaveLength(0);
        expect(lastModel()).toEqual([]);
        expect(wrapper.emitted('busy').at(-1)[0]).toBe(false);
    });

    it('follows files the parent drops from the v-model, and keeps the uploads', async () => {
        mountField({ modelValue: [uploaded(1, 'old.pdf'), uploaded(2, 'other.pdf')] });

        wrapper.vm.addFiles([browserFile('a.pdf')]);
        await nextTick();
        await wrapper.setProps({ modelValue: [uploaded(2, 'other.pdf')] });

        expect(rows().map((row) => row.text())).toEqual([
            expect.stringContaining('other.pdf'),
            expect.stringContaining('a.pdf'),
        ]);
        expect(rowOf('a.pdf').classes('is-uploading')).toBe(true);
        expect(wrapper.emitted('update:modelValue')).toBeUndefined();
    });

    it('shows files the parent adds to the v-model', async () => {
        mountField({ modelValue: [uploaded(1, 'old.pdf')] });

        await wrapper.setProps({ modelValue: [uploaded(1, 'old.pdf'), uploaded(2, 'new.pdf')] });

        expect(rows().map((row) => row.attributes('data-preview-guid'))).toEqual(['guid-1', 'guid-2']);
        expect(wrapper.emitted('update:modelValue')).toBeUndefined();
    });

    it('cancels its uploads when it goes', async () => {
        mountField();

        wrapper.vm.addFiles([browserFile('a.pdf'), browserFile('b.pdf')]);
        await nextTick();
        const field = wrapper;
        wrapper.unmount();
        wrapper = undefined;
        await flushPromises();

        expect(requests.map((request) => request.aborted)).toEqual([true, true]);
        expect(field.emitted('update:modelValue')).toBeUndefined();
    });

    it('has no batch progress bar and no legacy file list', async () => {
        mountField({ modelValue: [uploaded(1, 'old.pdf')] });

        wrapper.vm.addFiles([browserFile('a.pdf')]);
        await nextTick();

        expect(wrapper.find('.progress').exists()).toBe(false);
        expect(wrapper.find('ul.files').exists()).toBe(false);
        expect(wrapper.find('.list-group.file-list').exists()).toBe(true);
        expect(rows().map((row) => row.find('button.file-row-action').attributes('aria-label')))
            .toEqual(['Remove old.pdf', 'Remove a.pdf']);
    });

    it('uploads files dropped onto the field', async () => {
        mountField();

        await wrapper.find('.vue-upload-field').trigger('drop', {
            dataTransfer: { files: [browserFile('dropped.pdf')] },
        });

        expect(requests.map(fileNameOf)).toEqual(['dropped.pdf']);
        expect(rows()).toHaveLength(1);
    });

    it('uploads files pasted into the field', async () => {
        mountField();

        await wrapper.find('.vue-upload-field').trigger('paste', {
            clipboardData: { files: [browserFile('pasted.png')] },
        });

        expect(requests.map(fileNameOf)).toEqual(['pasted.png']);
    });

    it('accepts already-uploaded files handed over by a legacy file handler event', async () => {
        mountField();

        wrapper.element.dispatchEvent(new CustomEvent('humhub:file:attach', {
            detail: { files: [apiFile({ id: 9, guid: 'guid-9', fileName: 'from-cloud.docx' })] },
        }));
        await flushPromises();

        expect(requests).toHaveLength(0);
        expect(wrapper.emitted('update:modelValue')[0][0]).toEqual([
            apiFile({ id: 9, guid: 'guid-9', fileName: 'from-cloud.docx' }),
        ]);
        expect(rowOf('from-cloud.docx')).toBeDefined();
    });

    it('takes the file a module\'s handler created, once its entry was clicked', async () => {
        mountField({
            handlers: [{ label: 'New text file', icon: 'file-text', attributes: { 'data-action-click': 'texteditor.createSubmit' } }],
        });
        const created = { guid: 'guid-7', name: 'notes.txt', mimeIcon: 'mime-text' };

        // Not this field's: nothing was asked of it
        globalThis.humhubStubs.event.trigger('humhub:file:created', [created]);
        expect(wrapper.emitted('update:modelValue')).toBeUndefined();

        await wrapper.find('.dropdown-menu a.dropdown-item').trigger('click');
        globalThis.humhubStubs.event.trigger('humhub:file:created', [created]);
        globalThis.humhubStubs.event.trigger('humhub:file:created', [{ guid: 'guid-8', name: 'other.txt' }]);

        expect(wrapper.emitted('update:modelValue')).toHaveLength(1);
        expect(wrapper.emitted('update:modelValue')[0][0]).toEqual([{ ...created, fileName: 'notes.txt' }]);
    });

    it('leaves the file to whoever\'s handler entry was clicked after its own', async () => {
        mountField({
            handlers: [{ label: 'New text file', icon: 'file-text', attributes: { 'data-action-click': 'texteditor.createSubmit' } }],
        });
        await wrapper.find('.dropdown-menu a.dropdown-item').trigger('click');

        // An editor's menu ran a handler since
        const elsewhere = document.createElement('a');
        elsewhere.setAttribute('data-action-process', 'file-handler');
        document.body.appendChild(elsewhere);
        elsewhere.click();
        globalThis.humhubStubs.event.trigger('humhub:file:created', [{ guid: 'guid-7', name: 'notes.txt' }]);

        expect(wrapper.emitted('update:modelValue')).toBeUndefined();
        elsewhere.remove();
    });

    it('opens the picker with the accept type of an upload-by-type handler entry', async () => {
        mountField({
            handlers: [{ label: 'Image', icon: 'camera', attributes: { 'data-action-click': 'file.uploadByType', 'data-action-params': '{"type":"image/*"}' } }],
        });

        const input = wrapper.find('input[type="file"]');
        const click = vi.spyOn(input.element, 'click');

        await wrapper.find('.dropdown-menu a.dropdown-item').trigger('click');
        // The accept attribute has to reach the DOM before the picker opens, so the click
        // itself happens one tick later (see openPicker()).
        await flushPromises();

        expect(click).toHaveBeenCalledTimes(1);
        expect(input.attributes('accept')).toBe('image/*');
    });

    it('keeps the click away from the legacy action delegate, which would open a second picker', async () => {
        mountField({
            handlers: [{ label: 'Image', icon: 'camera', attributes: { 'data-action-click': 'file.uploadByType', 'data-action-params': '{"type":"image/*"}' } }],
        });

        // `humhub.action.js` listens on the document; browser-verified, letting the click
        // through there opened a SECOND, unfiltered picker in the same gesture - which the
        // browser then dropped, so the entry appeared to do nothing at all.
        const delegate = vi.fn();
        document.addEventListener('click', delegate);

        await wrapper.find('.dropdown-menu a.dropdown-item').trigger('click');
        await flushPromises();

        document.removeEventListener('click', delegate);
        expect(delegate).not.toHaveBeenCalled();
    });

    describe('focus', () => {
        it('hands the focus to the upload button when the last row goes', async () => {
            mountField({ modelValue: [uploaded(1, 'old.pdf')] }, { attachTo: document.body });

            const remove = rowOf('old.pdf').find('button.file-row-action');
            remove.element.focus();
            await remove.trigger('click');
            await flushPromises();

            expect(document.activeElement).toBe(wrapper.find('.fileinput-button').element);
        });

        it('finds the upload button where it was teleported to', async () => {
            const buttons = document.createElement('div');
            document.body.appendChild(buttons);
            mountField({ triggerTarget: buttons }, { attachTo: document.body });

            wrapper.vm.addFiles([browserFile('a.pdf')]);
            await nextTick();
            const remove = rowOf('a.pdf').find('button.file-row-action');
            remove.element.focus();
            await remove.trigger('click');
            await flushPromises();

            expect(document.activeElement).toBe(buttons.querySelector('.fileinput-button'));

            document.body.focus();
            wrapper.vm.focus();
            expect(document.activeElement).toBe(buttons.querySelector('.fileinput-button'));
        });

        it('leaves the focus alone when it was not in the list', async () => {
            const outside = document.createElement('button');
            document.body.appendChild(outside);
            mountField({ modelValue: [uploaded(1, 'old.pdf')] }, { attachTo: document.body });

            outside.focus();
            await removeRow('old.pdf');
            await flushPromises();

            expect(document.activeElement).toBe(outside);
        });
    });

    describe('in a form that is busy while the field uploads', () => {
        const field = () => wrapper.findComponent(UploadField);

        it('still takes files and removes rows while it uploads', async () => {
            wrapper = mount(FormHost, { global });

            field().vm.addFiles([browserFile('a.pdf'), browserFile('b.pdf')]);
            await flushPromises();
            succeed('a.pdf', uploaded(1, 'a.pdf'));
            await flushPromises();

            // b.pdf still uploads, so the form is busy
            expect(wrapper.find('.fileinput-button').attributes('aria-disabled')).toBe('false');

            field().vm.addFiles([browserFile('c.pdf')]);
            await flushPromises();
            expect(requests.map(fileNameOf)).toEqual(['a.pdf', 'b.pdf', 'c.pdf']);

            await removeRow('b.pdf');
            await flushPromises();
            expect(requestFor('b.pdf').aborted).toBe(true);

            await removeRow('a.pdf');
            await flushPromises();
            expect(field().emitted('update:modelValue').at(-1)[0]).toEqual([]);
            expect(rows().map((row) => row.text())).toEqual([expect.stringContaining('c.pdf')]);
        });

        it('takes no files while the form is busy with something else', async () => {
            wrapper = mount(FormHost, { props: { formBusy: true }, global });

            field().vm.addFiles([browserFile('a.pdf')]);
            await flushPromises();

            expect(requests).toHaveLength(0);
            expect(rows()).toHaveLength(0);
            expect(wrapper.find('.fileinput-button').attributes('aria-disabled')).toBe('true');
        });

        it('shows the errors the form assigns to the attribute', async () => {
            wrapper = mount(FormHost, { global });

            wrapper.findComponent(HumHubForm).vm.setErrors({ errors: { fileList: ['A file was not found.'] } });
            await nextTick();

            expect(wrapper.find('.vue-upload-field .invalid-feedback').text()).toBe('A file was not found.');
        });
    });
});
