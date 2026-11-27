import { afterEach, beforeAll, beforeEach, describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import { nextTick, TransitionGroup } from 'vue';
import FileRow from '../../vue/FileRow.vue';
import UploadList from '../../vue/UploadList.vue';
import { UPLOAD_EVENTS } from '../../vue/upload/uploadBridge.js';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const vueModule = globalThis.humhub.modules.vue;

// The API's file shape (file\serializers\FileSerializer::file())
const uploaded = (guid, fileName) => ({
    id: 1,
    guid,
    mimeType: 'text/plain',
    size: 1024,
    fileName,
    icon: 'file-text',
    url: `/file/file/download?guid=${guid}`,
    downloadUrl: `/file/file/download?download=1&guid=${guid}`,
    previewUrl: null,
});

// A file picked in a form, before the server answered
const staged = (fileName) => ({ fileName, size: 2048 });

// The island mounter registers v-additions per app; the real TransitionGroup, so leaving rows stay a moment as in
// the browser
const global = {
    components: { FileRow },
    directives: { additions: {} },
    stubs: { TransitionGroup: false },
};

const render = (props) => mount(UploadList, { props, global, attachTo: document.body });

const removeButton = (wrapper, index) => wrapper.findAll('.file-row')[index].find('button.file-row-action');

// The rows that are not on their way out
const currentRows = (root) => Array.from(root.querySelectorAll('.file-row:not(.file-row-leave-active)'));

let announce;

beforeEach(() => {
    document.body.replaceChildren();
    announce = vi.spyOn(vueModule, 'announce').mockImplementation(() => {});
});

afterEach(() => {
    vi.useRealTimers();
    vi.restoreAllMocks();
});

describe('UploadList', () => {
    it('renders a file row per item in a list', () => {
        const items = [
            { key: 'g1', file: uploaded('g1', 'notes.txt') },
            { key: 2, file: staged('report.pdf'), progress: 30 },
            { key: 3, file: staged('tool.exe'), error: 'File type not allowed' },
        ];
        const wrapper = render({ items });

        const group = wrapper.findComponent(TransitionGroup);
        expect(group.props('name')).toBe('file-row');

        const list = wrapper.find('.list-group.file-list');
        expect(list.attributes('hidden')).toBeUndefined();
        // The transition group is the list itself, the rows its items
        const rows = list.find(':scope > div.file-list-rows[role="list"]');
        expect(rows.element).toBe(group.element);
        expect(rows.findAll(':scope > .file-row[role="listitem"]')).toHaveLength(3);

        const fileRows = wrapper.findAllComponents(FileRow);
        expect(fileRows.map((row) => row.props('action'))).toEqual(['remove', 'remove', 'remove']);
        expect(fileRows.map((row) => row.props('progress'))).toEqual([null, 30, null]);
        expect(fileRows.map((row) => row.props('error'))).toEqual([null, null, 'File type not allowed']);
        expect(fileRows[0].props('file').fileName).toBe('notes.txt');
    });

    it('passes the placement of the image preview on to its rows', () => {
        const wrapper = render({ items: [{ key: 'g1', file: uploaded('g1', 'notes.txt') }], popoverPlacement: 'top' });

        expect(wrapper.findComponent(FileRow).props('popoverPlacement')).toBe('top');
    });

    it('passes the download action on to its rows', () => {
        const wrapper = render({ items: [{ key: 'g1', file: uploaded('g1', 'notes.txt') }], action: 'download' });

        expect(wrapper.findComponent(FileRow).props('action')).toBe('download');
        expect(wrapper.find('a.file-row-action').exists()).toBe(true);
    });

    it('emits remove with the item and leaves the removing to its owner', async () => {
        const items = [
            { key: 1, file: staged('a.pdf'), progress: 10 },
            { key: 2, file: staged('b.pdf'), progress: 20 },
        ];
        const wrapper = render({ items });

        await removeButton(wrapper, 1).trigger('click');

        expect(wrapper.emitted('remove')).toHaveLength(1);
        expect(wrapper.emitted('remove')[0][0]).toEqual(items[1]);
        expect(wrapper.findAll('.file-row')).toHaveLength(2);
    });

    it('is hidden without rows and shows when the first one comes', async () => {
        const wrapper = render({ items: [] });

        expect(wrapper.find('.list-group.file-list').attributes('hidden')).toBeDefined();
        expect(wrapper.findComponent(TransitionGroup).exists()).toBe(true);

        await wrapper.setProps({ items: [{ key: 1, file: staged('a.pdf'), progress: 0 }] });

        expect(wrapper.find('.list-group.file-list').attributes('hidden')).toBeUndefined();
        expect(wrapper.findAll('.file-row')).toHaveLength(1);
    });

    describe('announcements', () => {
        beforeEach(() => {
            vi.useFakeTimers({ toFake: ['setTimeout', 'clearTimeout'] });
        });

        it('says once that a file has been uploaded', async () => {
            const wrapper = render({ items: [{ key: 1, file: staged('a.pdf'), progress: 0 }] });

            await wrapper.setProps({ items: [{ key: 1, file: staged('a.pdf'), progress: 80 }] });
            await vi.runAllTimersAsync();
            expect(announce).not.toHaveBeenCalled();

            await wrapper.setProps({ items: [{ key: 1, file: uploaded('g1', 'a.pdf'), progress: null }] });
            await vi.runAllTimersAsync();
            expect(announce).toHaveBeenCalledExactlyOnceWith('Uploaded a.pdf');

            await wrapper.setProps({ items: [{ key: 1, file: uploaded('g1', 'a.pdf') }, { key: 2, file: staged('b.pdf'), progress: 0 }] });
            await vi.runAllTimersAsync();
            expect(announce).toHaveBeenCalledOnce();
        });

        it('says why a file was refused, also for a file that comes refused', async () => {
            const wrapper = render({ items: [{ key: 1, file: staged('a.exe'), progress: 40 }] });

            await wrapper.setProps({ items: [{ key: 1, file: staged('a.exe'), progress: null, error: 'File type not allowed' }] });
            await vi.runAllTimersAsync();
            expect(announce).toHaveBeenCalledExactlyOnceWith('Could not upload a.exe: File type not allowed');

            await wrapper.setProps({
                items: [
                    { key: 1, file: staged('a.exe'), error: 'File type not allowed' },
                    { key: 2, file: staged('huge.iso'), error: 'Too large' },
                ],
            });
            await vi.runAllTimersAsync();
            expect(announce).toHaveBeenCalledTimes(2);
            expect(announce).toHaveBeenLastCalledWith('Could not upload huge.iso: Too large');
        });

        it('says nothing about the files it starts with, nor about files that come uploaded', async () => {
            const start = [
                { key: 'g1', file: uploaded('g1', 'old.txt') },
                { key: 2, file: staged('failed.exe'), error: 'File type not allowed' },
            ];
            const wrapper = render({ items: start });

            await wrapper.setProps({ items: [...start, { key: 'g3', file: uploaded('g3', 'added.txt') }] });
            await vi.runAllTimersAsync();

            expect(announce).not.toHaveBeenCalled();
        });

        it('says files that settle together in one message', async () => {
            const wrapper = render({
                items: [
                    { key: 1, file: staged('a.pdf'), progress: 50 },
                    { key: 2, file: staged('b.exe'), progress: 50 },
                    { key: 3, file: staged('c.pdf'), progress: 50 },
                ],
            });

            await wrapper.setProps({
                items: [
                    { key: 1, file: uploaded('g1', 'a.pdf') },
                    { key: 2, file: staged('b.exe'), error: 'File type not allowed' },
                    { key: 3, file: staged('c.pdf'), progress: 60 },
                ],
            });
            // `announce()` keeps only the last message it gets within its delay
            await wrapper.setProps({
                items: [
                    { key: 1, file: uploaded('g1', 'a.pdf') },
                    { key: 2, file: staged('b.exe'), error: 'File type not allowed' },
                    { key: 3, file: uploaded('g3', 'c.pdf') },
                ],
            });
            await vi.runAllTimersAsync();

            expect(announce).toHaveBeenCalledExactlyOnceWith(
                'Uploaded a.pdf Could not upload b.exe: File type not allowed Uploaded c.pdf',
            );
        });
    });

    describe('focus', () => {
        const items = [
            { key: 1, file: staged('a.pdf'), progress: 10 },
            { key: 2, file: staged('b.pdf'), progress: 20 },
            { key: 3, file: staged('c.pdf'), progress: 30 },
        ];

        it('hands the focus of a removed row on to the next row', async () => {
            const wrapper = render({ items });
            const next = removeButton(wrapper, 2).element;
            removeButton(wrapper, 1).element.focus();

            await wrapper.setProps({ items: [items[0], items[2]] });
            await nextTick();

            // The removed row is still collapsing; the focus is on the row that stays
            expect(document.activeElement).toBe(next);
        });

        it('hands it to the previous row when the last row goes', async () => {
            const wrapper = render({ items });
            const previous = removeButton(wrapper, 1).element;
            removeButton(wrapper, 2).element.focus();

            await wrapper.setProps({ items: [items[0], items[1]] });
            await nextTick();

            expect(document.activeElement).toBe(previous);
        });

        it('leaves the focus alone when it was not in the removed row', async () => {
            const outside = document.createElement('button');
            document.body.appendChild(outside);
            const wrapper = render({ items });
            outside.focus();

            await wrapper.setProps({ items: [items[0], items[2]] });
            await nextTick();

            expect(document.activeElement).toBe(outside);
        });
    });
});

describe('UploadList bridge', () => {
    beforeAll(() => {
        vueModule.register('FileRow', FileRow);
        vueModule.register('UploadList', UploadList);
    });

    // An island inside a container, as `file\widgets\FilePreview` renders it
    const mountIsland = async (props, { bridge = true } = {}) => {
        const container = document.createElement('div');
        const tag = document.createElement('upload-list');
        if (bridge) {
            tag.setAttribute('data-upload-bridge', '');
        }
        tag.setAttribute('props', JSON.stringify(props));
        container.appendChild(tag);
        document.body.appendChild(container);

        const ready = vi.fn();
        const removed = vi.fn();
        container.addEventListener(UPLOAD_EVENTS.READY, ready);
        container.addEventListener(UPLOAD_EVENTS.REMOVE, (event) => removed(event.detail));

        await vueModule.mountElement(tag);

        return { container, tag, ready, removed };
    };

    const send = async (tag, type, detail) => {
        tag.dispatchEvent(new CustomEvent(type, { detail }));
        await nextTick();
    };

    it('keeps its event names in one place', () => {
        expect(UPLOAD_EVENTS).toEqual({
            ITEMS: 'upload:items',
            ADD: 'upload:add',
            PROGRESS: 'upload:progress',
            DONE: 'upload:done',
            FAIL: 'upload:fail',
            RESET: 'upload:reset',
            READY: 'upload:ready',
            REMOVE: 'upload:remove',
        });
        expect(Object.isFrozen(UPLOAD_EVENTS)).toBe(true);
    });

    it('renders the files of its props at once and says it is ready', async () => {
        const { tag, ready } = await mountIsland({ items: [{ key: 'g1', file: uploaded('g1', 'old.txt') }] });

        expect(ready).toHaveBeenCalledOnce();
        expect(tag.hasAttribute('data-upload-ready')).toBe(true);
        // Mounted into the tag: the list is its child
        expect(tag.querySelector(':scope > .list-group.file-list')).not.toBeNull();
        expect(currentRows(tag)).toHaveLength(1);
        expect(tag.querySelector('a.file-row-name').getAttribute('href')).toBe('/file/file/download?guid=g1');
    });

    it('follows an upload from the host\'s events', async () => {
        const { tag } = await mountIsland({ items: [] });

        await send(tag, UPLOAD_EVENTS.ADD, { key: 1, file: staged('new.pdf') });
        let row = currentRows(tag)[0];
        expect(row.classList.contains('is-uploading')).toBe(true);
        expect(row.querySelector('[role="progressbar"]').getAttribute('aria-valuenow')).toBe('0');

        await send(tag, UPLOAD_EVENTS.PROGRESS, { key: 1, percent: 60 });
        expect(row.querySelector('[role="progressbar"]').getAttribute('aria-valuenow')).toBe('60');

        await send(tag, UPLOAD_EVENTS.DONE, { key: 1, file: uploaded('g2', 'new.pdf') });
        row = currentRows(tag)[0];
        expect(row.classList.contains('is-uploading')).toBe(false);
        expect(row.querySelector('a.file-row-name').getAttribute('href')).toBe('/file/file/download?guid=g2');

        // A late progress does not start it again
        await send(tag, UPLOAD_EVENTS.PROGRESS, { key: 1, percent: 100 });
        expect(currentRows(tag)[0].classList.contains('is-uploading')).toBe(false);

        await send(tag, UPLOAD_EVENTS.ADD, { key: 2, file: staged('tool.exe') });
        await send(tag, UPLOAD_EVENTS.FAIL, { key: 2, message: 'File type not allowed' });
        row = currentRows(tag)[1];
        expect(row.classList.contains('is-invalid')).toBe(true);
        expect(row.querySelector('.invalid-feedback').textContent).toBe('File type not allowed');

        // An uploaded file the host adds itself
        await send(tag, UPLOAD_EVENTS.ADD, { key: 'g3', file: uploaded('g3', 'module.txt'), progress: null });
        expect(currentRows(tag)[2].querySelector('a.file-row-name')).not.toBeNull();
    });

    it('asks the host to remove a row and leaves it until the host says so', async () => {
        const file = uploaded('g1', 'old.txt');
        const { tag, removed } = await mountIsland({ items: [{ key: 'g1', file }] });

        tag.querySelector('button.file-row-action').click();
        await nextTick();

        expect(removed).toHaveBeenCalledExactlyOnceWith({ key: 'g1', file });
        expect(currentRows(tag)).toHaveLength(1);

        await send(tag, UPLOAD_EVENTS.ITEMS, []);
        expect(currentRows(tag)).toHaveLength(0);
    });

    it('hands the focus on when the host drops the row', async () => {
        const items = [
            { key: 'g1', file: uploaded('g1', 'a.txt') },
            { key: 'g2', file: uploaded('g2', 'b.txt') },
        ];
        const { tag, removed } = await mountIsland({ items });
        const [first, second] = tag.querySelectorAll('button.file-row-action');
        first.focus();
        first.click();
        expect(removed).toHaveBeenCalledOnce();

        await send(tag, UPLOAD_EVENTS.ITEMS, [items[1]]);
        await nextTick();

        expect(document.activeElement).toBe(second);
    });

    it('shows a row whose file the host is deleting as busy', async () => {
        const file = uploaded('g1', 'old.txt');
        const { tag } = await mountIsland({ items: [{ key: 'g1', file }] });

        await send(tag, UPLOAD_EVENTS.ITEMS, [{ key: 'g1', file, busy: true }]);
        expect(tag.querySelector('button.file-row-action').getAttribute('aria-busy')).toBe('true');

        await send(tag, UPLOAD_EVENTS.ITEMS, [{ key: 'g1', file }]);
        expect(tag.querySelector('button.file-row-action').hasAttribute('aria-busy')).toBe(false);
    });

    it('replaces its rows with upload:items and clears them with upload:reset', async () => {
        const { tag } = await mountIsland({ items: [{ key: 'g1', file: uploaded('g1', 'old.txt') }] });

        await send(tag, UPLOAD_EVENTS.ITEMS, [
            { key: 'g1', file: uploaded('g1', 'old.txt') },
            { key: 5, file: staged('a.pdf'), progress: 10 },
        ]);
        expect(currentRows(tag)).toHaveLength(2);
        expect(currentRows(tag)[1].classList.contains('is-uploading')).toBe(true);

        await send(tag, UPLOAD_EVENTS.ITEMS, [{ key: 5, file: staged('a.pdf'), progress: 10 }]);
        expect(currentRows(tag)).toHaveLength(1);

        await send(tag, UPLOAD_EVENTS.RESET);
        expect(currentRows(tag)).toHaveLength(0);
        // Hidden once the last row has collapsed
        await vi.waitFor(() => expect(tag.querySelector('.list-group.file-list').hidden).toBe(true));
        expect(tag.querySelectorAll('.file-row')).toHaveLength(0);
    });

    it('announces what the host finishes, not the files it replays', async () => {
        vi.useFakeTimers({ toFake: ['setTimeout', 'clearTimeout'] });
        const { tag } = await mountIsland({ items: [{ key: 'g1', file: uploaded('g1', 'old.txt') }] });

        // The replay on upload:ready: the files of the form, one of them uploaded before the island was there
        await send(tag, UPLOAD_EVENTS.ITEMS, [
            { key: 'g1', file: uploaded('g1', 'old.txt') },
            { key: 4, file: uploaded('g4', 'early.txt') },
            { key: 5, file: staged('a.pdf'), progress: 10 },
        ]);
        await vi.runAllTimersAsync();
        expect(announce).not.toHaveBeenCalled();

        await send(tag, UPLOAD_EVENTS.DONE, { key: 5, file: uploaded('g5', 'a.pdf') });
        await vi.runAllTimersAsync();
        expect(announce).toHaveBeenCalledExactlyOnceWith('Uploaded a.pdf');
    });

    it('renders only the row whose upload progresses, and runs the additions only for a new file', async () => {
        // Each render of a row, by its file name; spied before the rows exist, as an instance keeps its render
        const rowRenders = vi.spyOn(FileRow, 'render');
        const listRenders = vi.spyOn(UploadList, 'render');
        const items = Array.from({ length: 10 }, (_, i) => ({ key: i + 1, file: staged(`f${i + 1}.pdf`), progress: 0 }));
        const { tag } = await mountIsland({ items });
        await nextTick();
        const applyTo = vi.spyOn(globalThis.humhubStubs.additions, 'applyTo');
        const measure = vi.spyOn(Element.prototype, 'getBoundingClientRect');
        rowRenders.mockClear();
        listRenders.mockClear();

        for (const percent of [10, 20, 20, 30]) {
            await send(tag, UPLOAD_EVENTS.PROGRESS, { key: 3, percent });
        }

        expect(rowRenders.mock.calls.map(([row]) => row.file.fileName)).toEqual(['f3.pdf', 'f3.pdf', 'f3.pdf']);
        expect(listRenders).not.toHaveBeenCalled();
        expect(applyTo).not.toHaveBeenCalled();
        expect(measure).not.toHaveBeenCalled();
        expect(currentRows(tag)[2].querySelector('[role="progressbar"]').getAttribute('aria-valuenow')).toBe('30');

        // Uploaded: the row has its preview popover now
        rowRenders.mockClear();
        await send(tag, UPLOAD_EVENTS.DONE, { key: 3, file: { ...uploaded('g3', 'f3.pdf'), previewUrl: '/preview/g3' } });

        expect(rowRenders.mock.calls.map(([row]) => row.file.fileName)).toEqual(['f3.pdf']);
        expect(listRenders).not.toHaveBeenCalled();
        expect(applyTo).toHaveBeenCalledOnce();
        expect(applyTo.mock.calls[0][0][0]).toBe(currentRows(tag)[2]);
        expect(currentRows(tag)[2].querySelector('.po')).not.toBeNull();
    });

    it('has no bridge on a tag without data-upload-bridge', async () => {
        const { tag, ready } = await mountIsland({ items: [] }, { bridge: false });

        await send(tag, UPLOAD_EVENTS.ADD, { key: 1, file: staged('a.pdf') });

        expect(ready).not.toHaveBeenCalled();
        expect(tag.hasAttribute('data-upload-ready')).toBe(false);
        expect(currentRows(tag)).toHaveLength(0);
    });

    it('stops listening when it is unmounted', async () => {
        const { tag } = await mountIsland({ items: [] });

        vueModule.unmountElement(tag);
        await send(tag, UPLOAD_EVENTS.ADD, { key: 1, file: staged('a.pdf') });

        expect(tag.hasAttribute('data-upload-ready')).toBe(false);
        expect(tag.querySelectorAll('.file-row')).toHaveLength(0);
    });
});
