import { afterEach, describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import FileRow from '../../vue/FileRow.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

// The API's file shape (file\serializers\FileSerializer::file()).
const file = (overrides = {}) => ({
    id: 1,
    guid: 'file-guid-1',
    mimeType: 'text/plain',
    size: 2048,
    fileName: 'notes.txt',
    icon: 'file-text',
    url: '/file/file/download?guid=file-guid-1',
    downloadUrl: '/file/file/download?download=1&guid=file-guid-1',
    previewUrl: null,
    ...overrides,
});

// A file picked in a form, before the server answered
const staged = () => ({ fileName: 'report.pdf', size: 3 * 1024 * 1024 });

const render = (props) => mount(FileRow, { props });

// The name shortened in the middle, as text
const nameOf = (row) => row.find('.file-row-name-head').text() + row.find('.file-row-name-tail').text();

afterEach(() => {
    vi.restoreAllMocks();
});

describe('FileRow', () => {
    it('links the name and offers the download by default', () => {
        const row = render({ file: file() });

        expect(row.classes()).toEqual(expect.arrayContaining(['list-group-item', 'file-row']));
        expect(row.classes()).not.toContain('is-uploading');
        expect(row.classes()).not.toContain('is-invalid');
        expect(row.attributes('role')).toBe('listitem');
        expect(row.attributes('data-preview-guid')).toBe('file-guid-1');
        expect(row.find('.file-row-icon').classes()).toContain('ti-file-text');
        expect(row.find('.file-row-meta').text()).toBe('(2 KB)');

        const link = row.find('a.file-row-name');
        expect(link.attributes('href')).toBe('/file/file/download?guid=file-guid-1');
        expect(link.attributes('data-file-download')).toBeDefined();
        expect(nameOf(row)).toBe('notes.txt');

        const download = row.find('a.file-row-action');
        expect(download.attributes('href')).toBe('/file/file/download?download=1&guid=file-guid-1');
        expect(download.attributes('aria-label')).toBe('Download notes.txt');
        expect(download.find('.ti-download').exists()).toBe(true);
        expect(row.find('button').exists()).toBe(false);
        expect(row.find('[role="progressbar"]').exists()).toBe(false);
        expect(row.find('.invalid-feedback').exists()).toBe(false);
    });

    it('offers remove instead of the download and emits it', async () => {
        const t = vi.spyOn(globalThis.humhubStubs.i18n, 't');
        const row = render({ file: file(), action: 'remove' });

        expect(row.find('a.file-row-action').exists()).toBe(false);
        // The uploaded file keeps its link
        expect(row.find('a.file-row-name').exists()).toBe(true);

        const remove = row.find('button.file-row-action');
        // Never submits the form it sits in
        expect(remove.attributes('type')).toBe('button');
        expect(remove.classes()).toEqual(
            expect.arrayContaining(['btn', 'btn-ghost', 'btn-ghost-danger', 'btn-sm', 'btn-icon-only']),
        );
        expect(remove.find('i.ti.ti-trash').attributes('aria-hidden')).toBe('true');
        expect(remove.attributes('aria-label')).toBe('Remove notes.txt');
        // The tooltip as Button::iconOnly() sets it, no native title beside it
        expect(remove.attributes('data-bs-title')).toBe('Remove notes.txt');
        expect(remove.attributes('data-bs-toggle')).toBe('tooltip');
        expect(remove.attributes('title')).toBeUndefined();
        expect(t).toHaveBeenCalledWith('base', 'Remove {label}', { label: 'notes.txt' });

        await remove.trigger('click');

        expect(row.emitted('remove')).toHaveLength(1);
    });

    it('shows a remove under way on the button, which keeps the focus', async () => {
        const row = mount(FileRow, { props: { file: file(), action: 'remove', busy: true }, attachTo: document.body });
        const remove = row.find('button.file-row-action');
        remove.element.focus();

        // Not `disabled`: a focused button that gets disabled loses the focus
        expect(remove.attributes('disabled')).toBeUndefined();
        expect(remove.attributes('aria-disabled')).toBe('true');
        expect(remove.attributes('aria-busy')).toBe('true');
        expect(remove.find('.spinner-border.spinner-border-sm').attributes('aria-hidden')).toBe('true');
        expect(remove.find('.ti-trash').exists()).toBe(false);
        expect(remove.attributes('aria-label')).toBe('Remove notes.txt');

        await remove.trigger('click');
        expect(row.emitted('remove')).toBeUndefined();
        expect(document.activeElement).toBe(remove.element);

        await row.setProps({ busy: false });
        expect(remove.attributes('aria-disabled')).toBeUndefined();
        expect(remove.attributes('aria-busy')).toBeUndefined();
        expect(remove.find('.ti-trash').exists()).toBe(true);
        row.unmount();
    });

    it('runs a progress frame around the row while the file uploads', () => {
        const t = vi.spyOn(globalThis.humhubStubs.i18n, 't');
        const row = render({ file: staged(), action: 'remove', progress: 40 });

        expect(row.classes()).toContain('is-uploading');
        const frame = row.find('svg.progress-frame.file-row-progress');
        expect(frame.attributes('role')).toBe('progressbar');
        expect(frame.attributes('aria-valuenow')).toBe('40');
        expect(frame.attributes('aria-label')).toBe('Uploading report.pdf');
        expect(t).toHaveBeenCalledWith('FileModule.base', 'Uploading {fileName}', { fileName: 'report.pdf' });

        // The name is no link yet, still shortened in the middle
        expect(row.find('a').exists()).toBe(false);
        expect(row.find('span.file-row-name').exists()).toBe(true);
        expect(nameOf(row)).toBe('report.pdf');
        expect(row.find('[data-file-download]').exists()).toBe(false);
        // Removing cancels the upload
        expect(row.find('button.file-row-action').exists()).toBe(true);
    });

    it('shows that the server processes the file once it is sent', () => {
        const t = vi.spyOn(globalThis.humhubStubs.i18n, 't');
        const row = render({ file: staged(), action: 'remove', progress: 100 });

        expect(row.classes()).toEqual(expect.arrayContaining(['is-uploading', 'is-processing']));
        const frame = row.find('svg.progress-frame.file-row-progress');
        expect(frame.classes()).toContain('is-indeterminate');
        expect(frame.attributes('role')).toBe('progressbar');
        expect(frame.attributes('aria-valuenow')).toBeUndefined();
        expect(frame.attributes('aria-label')).toBe('Processing report.pdf');
        expect(t).toHaveBeenCalledWith('FileModule.base', 'Processing {fileName}', { fileName: 'report.pdf' });
        // Still no link, and removing still cancels
        expect(row.find('a').exists()).toBe(false);
        expect(row.find('button.file-row-action').exists()).toBe(true);
    });

    it('is not processing while the file is sent', () => {
        const row = render({ file: staged(), action: 'remove', progress: 99 });

        expect(row.classes()).not.toContain('is-processing');
        expect(row.find('.progress-frame').classes()).not.toContain('is-indeterminate');
    });

    it('treats a progress of 0 as uploading', () => {
        const row = render({ file: staged(), action: 'remove', progress: 0 });

        expect(row.classes()).toContain('is-uploading');
        expect(row.find('[role="progressbar"]').attributes('aria-valuenow')).toBe('0');
    });

    it('shows an uploading file with a URL as text, without download', () => {
        const row = render({ file: file(), progress: 90 });

        expect(row.find('a').exists()).toBe(false);
        expect(row.find('[data-file-download]').exists()).toBe(false);
        expect(row.find('.file-row-action').exists()).toBe(false);
    });

    it('shows the reason of a refused file in the row', () => {
        const row = render({ file: staged(), action: 'remove', error: 'File type not allowed' });

        expect(row.classes()).toContain('is-invalid');
        const reason = row.find('.file-row-text .invalid-feedback');
        expect(reason.text()).toBe('File type not allowed');
        // Bootstrap shows the feedback only beside an invalid field, the row shows it itself
        expect(reason.classes()).toContain('d-block');
        expect(row.find('a').exists()).toBe(false);
        expect(nameOf(row)).toBe('report.pdf');
        // The row stays until it is removed
        expect(row.find('button.file-row-action').exists()).toBe(true);
    });

    it('drops the progress frame once the upload failed', () => {
        const row = render({ file: staged(), action: 'remove', progress: 100, error: 'Too large' });

        expect(row.classes()).toContain('is-invalid');
        expect(row.classes()).not.toContain('is-uploading');
        expect(row.find('[role="progressbar"]').exists()).toBe(false);
    });

    it('shows a staged file with the generic icon and without links', () => {
        const row = render({ file: staged() });

        expect(row.find('.file-row-icon').classes()).toContain('ti-file');
        expect(row.find('.file-row-meta').text()).toBe('(3 MB)');
        expect(row.attributes('data-preview-guid')).toBeUndefined();
        expect(row.find('a').exists()).toBe(false);
        expect(row.find('span.file-row-name').exists()).toBe(true);
        // Nothing to download yet
        expect(row.find('.file-row-action').exists()).toBe(false);
    });

    it('opens the image preview on hover where it is told, or not at all', () => {
        const photo = file({ fileName: 'photo.jpg', previewUrl: '/preview/photo.jpg' });

        expect(render({ file: photo }).find('.file-row-text').attributes('data-bs-placement')).toBe('right');

        const top = render({ file: photo, popoverPlacement: 'top' }).find('.file-row-text');
        expect(top.classes()).toContain('po');
        expect(top.attributes('data-bs-placement')).toBe('top');

        const none = render({ file: photo, popoverPlacement: '' }).find('.file-row-text');
        expect(none.classes()).not.toContain('po');
        expect(none.attributes('data-bs-toggle')).toBeUndefined();
    });
});
