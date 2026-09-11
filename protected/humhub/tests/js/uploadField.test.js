import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import UploadField from '../../vue/UploadField.vue';
import HumHubForm from '../../vue/HumHubForm.vue';

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

const apiFile = (overrides = {}) => ({
    id: 7,
    guid: 'guid-7',
    fileName: 'report.pdf',
    mimeType: 'application/pdf',
    size: 2048,
    mimeIcon: 'mime-pdf',
    url: 'https://example.com/file/file/download?guid=guid-7',
    previewUrl: null,
    ...overrides,
});

const browserFile = (name = 'report.pdf') => new File(['content'], name, { type: 'application/pdf' });

let wrapper;
let postCalls;

const mountField = (props = {}, options = {}) => {
    wrapper = mount(UploadField, {
        props: { attribute: 'fileList', ...props },
        global: { directives: { additions: additionsDirective } },
        ...options,
    });
    return wrapper;
};

/** Mounts the field inside a HumHubForm, the way a real consumer does. */
const mountInForm = (fieldProps = {}) => {
    wrapper = mount(HumHubForm, {
        props: { modelName: 'Comment' },
        slots: {
            default: `<UploadField attribute="fileList" ${Object.entries(fieldProps)
                .map(([key, value]) => `:${key}="${value}"`)
                .join(' ')} />`,
        },
        global: {
            directives: { additions: additionsDirective },
            components: { UploadField },
        },
    });
    return wrapper;
};

beforeEach(() => {
    postCalls = [];
    globalThis.humhubStubs.client.post = (url, cfg) => {
        postCalls.push({ url, cfg });
        return Promise.resolve({ results: [apiFile()], errors: [] });
    };
});

afterEach(() => {
    wrapper?.unmount();
    wrapper = undefined;
});

describe('UploadField', () => {
    it('renders the legacy upload trigger markup with a hidden multi-file input', () => {
        mountField();

        const trigger = wrapper.find('.fileinput-button');
        expect(trigger.exists()).toBe(true);
        expect(trigger.find('i.fa.fa-cloud-upload').exists()).toBe(true);

        const input = wrapper.find('input[type="file"]');
        expect(input.exists()).toBe(true);
        expect(input.attributes('multiple')).toBeDefined();
    });

    it('uploads added files as one multipart request and emits the API shapes', async () => {
        mountField();

        await wrapper.vm.addFiles([browserFile('a.pdf'), browserFile('b.pdf')]);
        await flushPromises();

        expect(postCalls).toHaveLength(1);
        expect(postCalls[0].url).toContain('/api/v2/file');
        expect(postCalls[0].cfg.processData).toBe(false);
        expect(postCalls[0].cfg.contentType).toBe(false);
        expect(postCalls[0].cfg.data.getAll('files[]')).toHaveLength(2);

        const emitted = wrapper.emitted('update:modelValue');
        expect(emitted).toHaveLength(1);
        expect(emitted[0][0]).toEqual([apiFile()]);
    });

    it('appends to the files it was given instead of replacing them', async () => {
        const existing = apiFile({ id: 1, guid: 'guid-1', fileName: 'old.pdf' });
        mountField({ modelValue: [existing] });

        await wrapper.vm.addFiles([browserFile()]);
        await flushPromises();

        expect(wrapper.emitted('update:modelValue')[0][0]).toEqual([existing, apiFile()]);
    });

    it('reports busy while a request is in flight', async () => {
        let resolveRequest;
        globalThis.humhubStubs.client.post = () => new Promise((resolve) => {
            resolveRequest = () => resolve({ results: [apiFile()], errors: [] });
        });

        mountField();
        const promise = wrapper.vm.addFiles([browserFile()]);
        await flushPromises();

        expect(wrapper.emitted('busy').map((args) => args[0])).toEqual([true]);

        resolveRequest();
        await promise;
        await flushPromises();

        expect(wrapper.emitted('busy').map((args) => args[0])).toEqual([true, false]);
    });

    it('shows a per-file message for a rejected file and still keeps the accepted one', async () => {
        globalThis.humhubStubs.client.post = () => Promise.resolve({
            results: [apiFile()],
            errors: [{ fileName: 'huge.zip', messages: ['The file is too big.'] }],
        });

        mountField();
        await wrapper.vm.addFiles([browserFile(), browserFile('huge.zip')]);
        await flushPromises();

        expect(wrapper.emitted('update:modelValue')[0][0]).toEqual([apiFile()]);
        expect(wrapper.text()).toContain('huge.zip');
        expect(wrapper.text()).toContain('The file is too big.');
    });

    it('shows the request-level messages of a 422 response', async () => {
        globalThis.humhubStubs.client.post = () => Promise.reject({
            status: 422,
            errors: { files: ['No file was uploaded.'] },
        });

        mountField();
        await wrapper.vm.addFiles([browserFile()]);
        await flushPromises();

        expect(wrapper.text()).toContain('No file was uploaded.');
        expect(wrapper.emitted('update:modelValue')).toBeUndefined();
    });

    it('refuses files beyond the maximum without sending a request', async () => {
        mountField({ max: 2, modelValue: [apiFile({ id: 1, guid: 'guid-1' })] });

        await wrapper.vm.addFiles([browserFile('one.pdf'), browserFile('two.pdf')]);
        await flushPromises();

        expect(postCalls).toHaveLength(0);
        // The harness i18n stub does not expand ICU plurals, so the message is asserted by
        // its stable prefix rather than by the rendered count.
        expect(wrapper.text()).toContain('This upload field only allows a maximum of');
        expect(wrapper.emitted('update:modelValue')).toBeUndefined();
    });

    it('renders the legacy edit-mode preview markup for attached files', () => {
        mountField({ modelValue: [apiFile({ mimeIcon: 'mime-pdf' })] });

        const item = wrapper.find('ul.files li.file-preview-item');
        expect(item.exists()).toBe(true);
        expect(item.classes()).toContain('mime-pdf');
        expect(item.attributes('data-preview-guid')).toBe('guid-7');
        expect(item.text()).toContain('report.pdf');
        expect(item.find('.file_upload_remove_link').exists()).toBe(true);
    });

    it('removes a file locally, without deleting it on the server', async () => {
        const keep = apiFile({ id: 1, guid: 'guid-1', fileName: 'keep.pdf' });
        mountField({ modelValue: [keep, apiFile()] });

        await wrapper.findAll('.file_upload_remove_link')[1].trigger('click');

        expect(wrapper.emitted('update:modelValue')[0][0]).toEqual([keep]);
        expect(postCalls).toHaveLength(0);
    });

    it('uploads files dropped onto the field', async () => {
        mountField();

        await wrapper.find('.vue-upload-field').trigger('drop', {
            dataTransfer: { files: [browserFile('dropped.pdf')] },
        });
        await flushPromises();

        expect(postCalls).toHaveLength(1);
        expect(postCalls[0].cfg.data.getAll('files[]')).toHaveLength(1);
    });

    it('uploads files pasted into the field', async () => {
        mountField();

        await wrapper.find('.vue-upload-field').trigger('paste', {
            clipboardData: { files: [browserFile('pasted.png')] },
        });
        await flushPromises();

        expect(postCalls).toHaveLength(1);
    });

    it('accepts already-uploaded files handed over by a legacy file handler event', async () => {
        mountField();

        wrapper.element.dispatchEvent(new CustomEvent('humhub:file:attach', {
            detail: { files: [apiFile({ id: 9, guid: 'guid-9', fileName: 'from-cloud.docx' })] },
        }));
        await flushPromises();

        expect(postCalls).toHaveLength(0);
        expect(wrapper.emitted('update:modelValue')[0][0]).toEqual([
            apiFile({ id: 9, guid: 'guid-9', fileName: 'from-cloud.docx' }),
        ]);
    });

    it('opens the picker with the accept type of an upload-by-type handler entry', async () => {
        mountField({
            handlersHtml: '<li><a class="dropdown-item" data-action-click="file.uploadByType" '
                + 'data-action-params=\'{"type":"image/*"}\'>Image</a></li>',
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
            handlersHtml: '<li><a class="dropdown-item" data-action-click="file.uploadByType" '
                + 'data-action-params=\'{"type":"image/*"}\'>Image</a></li>',
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

    it('renders upload progress from the xhr progress event', async () => {
        mountField();
        wrapper.vm.addFiles([browserFile()]);
        await flushPromises();

        const xhr = postCalls[0].cfg.xhr();
        xhr.upload.dispatchEvent(Object.assign(new Event('progress'), {
            lengthComputable: true,
            loaded: 25,
            total: 100,
        }));
        await flushPromises();

        const bar = wrapper.find('.progress .progress-bar');
        expect(bar.exists()).toBe(true);
        expect(bar.element.style.width).toBe('25%');
    });

    it('routes upload errors into the surrounding form when there is one', async () => {
        globalThis.humhubStubs.client.post = () => Promise.reject({
            status: 422,
            errors: { files: ['Something went wrong.'] },
        });

        mountInForm();
        const field = wrapper.findComponent(UploadField);
        await field.vm.addFiles([browserFile()]);
        await flushPromises();

        expect(wrapper.text()).toContain('Something went wrong.');
    });
});
