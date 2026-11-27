import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import FileRow from '../../vue/FileRow.vue';
import * as audioPlayer from '../../vue/audio/audioPlayer.js';

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

const render = (props, options = {}) => mount(FileRow, { props, ...options });

// The name shortened in the middle, as text
const nameOf = (row) => row.find('.file-row-name-head').text() + row.find('.file-row-name-tail').text();

afterEach(() => {
    audioPlayer._reset();
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

describe('FileRow playable', () => {
    const song = (overrides = {}) => file({ guid: 'song', fileName: 'song.mp3', mimeType: 'audio/mpeg', icon: 'music', duration: 204, ...overrides });

    beforeEach(() => {
        vi.spyOn(HTMLMediaElement.prototype, 'load').mockImplementation(() => {});
        vi.spyOn(HTMLMediaElement.prototype, 'play').mockImplementation(function () {
            Object.defineProperty(this, 'paused', { value: false, configurable: true });
            this.dispatchEvent(new Event('play'));
            return Promise.resolve();
        });
        vi.spyOn(HTMLMediaElement.prototype, 'pause').mockImplementation(function () {
            Object.defineProperty(this, 'paused', { value: true, configurable: true });
            this.dispatchEvent(new Event('pause'));
        });
    });

    it('renders no play button unless playable', () => {
        const row = render({ file: song() });

        expect(row.find('.file-row-play').exists()).toBe(false);
        // The duration is shown for any file that has one
        expect(row.find('.file-row-meta').text()).toBe('(3:24 · 2 KB)');
        expect(render({ file: file() }).find('.file-row-meta').text()).toBe('(2 KB)');
    });

    it('renders the play button before the download, with labels', async () => {
        const row = render({ file: song(), playable: true });
        const play = row.find('button.file-row-play');

        expect(play.attributes('aria-label')).toBe('Play song.mp3');
        expect(play.attributes('data-bs-title')).toBe('Play song.mp3');
        expect(play.find('.ti-player-play').exists()).toBe(true);
        expect(row.find('.file-row-action').exists()).toBe(true);
        expect(row.find('.file-row-seek').exists()).toBe(false);

        await play.trigger('click');

        expect(row.find('.file-row-play').attributes('aria-label')).toBe('Pause song.mp3');
        expect(row.find('.file-row-play .ti-player-pause').exists()).toBe(true);
    });

    it('has no play button while the file is uploading or refused', () => {
        expect(render({ file: song(), playable: true, progress: 40 }).find('.file-row-play').exists()).toBe(false);
        expect(render({ file: song(), playable: true, error: 'Too big' }).find('.file-row-play').exists()).toBe(false);
    });

    it('shows time, seek line and aria while current', async () => {
        const row = render({ file: song(), playable: true });
        await row.find('.file-row-play').trigger('click');

        document.querySelector('audio').currentTime = 72;
        document.querySelector('audio').dispatchEvent(new Event('timeupdate'));
        await row.vm.$nextTick();

        const seek = row.find('input.file-row-seek');
        expect(row.classes()).toContain('is-current');
        expect(row.find('.file-row-meta').text()).toBe('(1:12 / 3:24)');
        expect(seek.attributes('min')).toBe('0');
        expect(seek.attributes('max')).toBe('204');
        expect(seek.attributes('step')).toBe('any');
        expect(seek.element.value).toBe('72');
        expect(seek.attributes('aria-label')).toBe('Position in song.mp3');
        expect(seek.attributes('aria-valuetext')).toBe('1:12 of 3:24');
    });

    it("prefers the server's duration to the browser's estimate", async () => {
        const browserSays = async (row, seconds) => {
            const audio = document.querySelector('audio');
            Object.defineProperty(audio, 'duration', { value: seconds, configurable: true });
            audio.dispatchEvent(new Event('durationchange'));
            await row.vm.$nextTick();
        };

        const known = render({ file: song(), playable: true });
        await known.find('.file-row-play').trigger('click');
        await browserSays(known, 188);
        expect(known.find('.file-row-meta').text()).toBe('(0:00 / 3:24)');
        expect(known.find('.file-row-seek').attributes('max')).toBe('204');
        known.unmount();

        const unknown = render({ file: song({ guid: 'other', duration: null }), playable: true });
        await unknown.find('.file-row-play').trigger('click');
        await browserSays(unknown, 188);
        expect(unknown.find('.file-row-meta').text()).toBe('(0:00 / 3:08)');
    });

    it('hands its playlist to the player and says when it becomes the current track', async () => {
        const next = song({ guid: 'next', fileName: 'next.mp3' });
        const first = render({ file: song(), playable: true, playlist: [song(), next] });
        const second = render({ file: next, playable: true, playlist: [song(), next] });

        await first.find('.file-row-play').trigger('click');
        expect(second.emitted('current')).toBeUndefined();

        vi.spyOn(globalThis.humhub.modules.vue, 'announce').mockImplementation(() => {});
        document.querySelector('audio').dispatchEvent(new Event('ended'));
        await second.vm.$nextTick();

        expect(audioPlayer.state.guid).toBe('next');
        expect(second.emitted('current')).toHaveLength(1);
        expect(second.classes()).toContain('is-current');
    });

    it('seeks by 5 seconds with the arrow keys and on input', async () => {
        const row = render({ file: song(), playable: true });
        await row.find('.file-row-play').trigger('click');
        const seek = row.find('.file-row-seek');

        await seek.trigger('keydown', { key: 'ArrowRight' });
        expect(audioPlayer.state.time).toBe(5);
        await seek.trigger('keydown', { key: 'ArrowRight' });
        await seek.trigger('keydown', { key: 'ArrowLeft' });
        expect(audioPlayer.state.time).toBe(5);

        seek.element.value = '100';
        await seek.trigger('input');
        expect(audioPlayer.state.time).toBe(100);
    });

    it('shows the reason for a file that can not be played, keeps the download, no invalid border', async () => {
        const row = render({ file: song(), playable: true });
        await row.find('.file-row-play').trigger('click');
        document.querySelector('audio').dispatchEvent(new Event('error'));
        await row.vm.$nextTick();

        expect(row.find('.file-row-reason').text()).toBe("This file can't be played.");
        expect(row.find('.file-row-play').exists()).toBe(false);
        expect(row.find('a.file-row-action').exists()).toBe(true);
        expect(row.classes()).not.toContain('is-invalid');
        expect(row.find('.file-row-seek').exists()).toBe(false);
    });

    it('reads the time alone without a duration', async () => {
        const row = render({ file: song({ duration: null }), playable: true });
        await row.find('.file-row-play').trigger('click');

        expect(row.find('.file-row-seek').attributes('aria-valuetext')).toBe('0:00');
    });

    it('seeks with the up and down arrows, and swaps left and right in a right-to-left layout', async () => {
        const row = render({ file: song(), playable: true }, { attachTo: document.body });
        await row.find('.file-row-play').trigger('click');
        const seek = row.find('.file-row-seek');

        await seek.trigger('keydown', { key: 'ArrowUp' });
        await seek.trigger('keydown', { key: 'ArrowUp' });
        expect(audioPlayer.state.time).toBe(10);
        await seek.trigger('keydown', { key: 'ArrowDown' });
        expect(audioPlayer.state.time).toBe(5);

        seek.element.style.direction = 'rtl';
        await seek.trigger('keydown', { key: 'ArrowLeft' });
        expect(audioPlayer.state.time).toBe(10);
        await seek.trigger('keydown', { key: 'ArrowRight' });
        expect(audioPlayer.state.time).toBe(5);
        row.unmount();
    });

    it('hands the focus from the seek line to the play button when the track ends', async () => {
        const row = render({ file: song() , playable: true }, { attachTo: document.body });
        await row.find('.file-row-play').trigger('click');
        row.find('.file-row-seek').element.focus();

        document.querySelector('audio').dispatchEvent(new Event('ended'));
        await row.vm.$nextTick();
        await row.vm.$nextTick();

        expect(row.find('.file-row-seek').exists()).toBe(false);
        expect(document.activeElement).toBe(row.find('.file-row-play').element);
        row.unmount();
    });

    it('hands the focus to the name link when the file fails, and announces it', async () => {
        const announce = vi.spyOn(globalThis.humhub.modules.vue, 'announce').mockImplementation(() => {});
        const row = render({ file: song(), playable: true }, { attachTo: document.body });
        await row.find('.file-row-play').trigger('click');
        row.find('.file-row-seek').element.focus();

        document.querySelector('audio').dispatchEvent(new Event('error'));
        await row.vm.$nextTick();
        await row.vm.$nextTick();

        expect(row.find('.file-row-play').exists()).toBe(false);
        expect(document.activeElement).toBe(row.find('a.file-row-name').element);
        expect(announce).toHaveBeenCalledExactlyOnceWith("song.mp3 can't be played.");
        row.unmount();
    });

    it('moves the focus off the play button of a file that fails', async () => {
        vi.spyOn(globalThis.humhub.modules.vue, 'announce').mockImplementation(() => {});
        const row = render({ file: song(), playable: true }, { attachTo: document.body });
        await row.find('.file-row-play').trigger('click');
        row.find('.file-row-play').element.focus();

        document.querySelector('audio').dispatchEvent(new Event('error'));
        await row.vm.$nextTick();
        await row.vm.$nextTick();

        expect(document.activeElement).toBe(row.find('a.file-row-name').element);
        row.unmount();
    });

    it('updates the tooltip of the play button when the label changes', async () => {
        const setContent = vi.fn();
        vi.stubGlobal('bootstrap', { Tooltip: { getInstance: () => ({ setContent }) } });
        const row = render({ file: song(), playable: true });

        await row.find('.file-row-play').trigger('click');

        expect(setContent).toHaveBeenCalledWith({ '.tooltip-inner': 'Pause song.mp3' });
        vi.unstubAllGlobals();
    });

    it('stops playing when the row unmounts', async () => {
        const row = render({ file: song(), playable: true });
        await row.find('.file-row-play').trigger('click');
        expect(audioPlayer.state.playing).toBe(true);

        row.unmount();

        expect(audioPlayer.state.guid).toBeNull();
    });
});
