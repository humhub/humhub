import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import * as player from '../../vue/audio/audioPlayer.js';
import { formatDuration } from '../../vue/audio/formatDuration.js';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const vueModule = globalThis.humhub.modules.vue;
const track = (guid) => ({ guid, url: `/file/download?guid=${guid}`, fileName: `${guid}.mp3`, duration: 100 });
const audioEl = () => document.querySelector('audio');

beforeEach(() => {
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

beforeEach(() => {
    // jsdom does not implement it
    vi.spyOn(HTMLMediaElement.prototype, 'load').mockImplementation(() => {});
});

afterEach(() => {
    player._reset();
    vi.restoreAllMocks();
});

describe('audioPlayer', () => {
    it('loads nothing before the first play and plays through one hidden element', () => {
        expect(audioEl()).toBeNull();

        player.toggle(track('a'));

        expect(audioEl().hidden).toBe(true);
        expect(audioEl().preload).toBe('none');
        expect(audioEl().getAttribute('src')).toBe('/file/download?guid=a');
        expect(player.state).toMatchObject({ guid: 'a', playing: true, duration: 100 });
    });

    it('pauses and resumes the same file', () => {
        player.toggle(track('a'));
        player.toggle(track('a'));
        expect(player.state).toMatchObject({ guid: 'a', playing: false });

        player.toggle(track('a'));
        expect(player.state.playing).toBe(true);
    });

    it('plays one file at a time', () => {
        player.toggle(track('a'));
        player.toggle(track('b'));

        expect(document.querySelectorAll('audio')).toHaveLength(1);
        expect(audioEl().getAttribute('src')).toBe('/file/download?guid=b');
        expect(player.state).toMatchObject({ guid: 'b', playing: true, time: 0 });
    });

    it('follows time and duration of the element and clamps seek', () => {
        player.toggle(track('a'));
        const el = audioEl();

        el.currentTime = 12;
        el.dispatchEvent(new Event('timeupdate'));
        expect(player.state.time).toBe(12);

        player.seek(500);
        expect(player.state.time).toBe(100);
        player.seek(-3);
        expect(player.state.time).toBe(0);
    });

    it('stops the current file when the last row showing it is released', () => {
        player.register('a');
        player.register('a');
        player.toggle(track('a'));

        player.release('a');
        expect(player.state.guid).toBe('a');

        player.release('a');
        expect(player.state).toMatchObject({ guid: null, playing: false });
    });

    it('does not stop for the release of another file', () => {
        player.register('b');
        player.toggle(track('a'));
        player.release('b');

        expect(player.state.guid).toBe('a');
    });

    it('resets when the file ended', () => {
        player.toggle(track('a'));
        audioEl().dispatchEvent(new Event('ended'));

        expect(player.state).toMatchObject({ guid: null, playing: false, time: 0 });
        expect(player.isFailed('a')).toBe(false);
    });

    it('keeps the server duration against the estimate of the browser, and fills a missing one', () => {
        const browserSays = (seconds) => {
            Object.defineProperty(audioEl(), 'duration', { value: seconds, configurable: true });
            audioEl().dispatchEvent(new Event('durationchange'));
            audioEl().dispatchEvent(new Event('loadedmetadata'));
        };

        player.toggle(track('a'));
        browserSays(88);
        expect(player.state.duration).toBe(100);
        player.seek(95);
        expect(player.state.time).toBe(95);

        player.toggle({ ...track('b'), duration: null });
        browserSays(88);
        expect(player.state.duration).toBe(88);
    });

    it('drops the source on stop, and takes the emptied source for no error', () => {
        player.toggle(track('a'));
        const el = audioEl();

        audioEl().dispatchEvent(new Event('ended'));
        el.dispatchEvent(new Event('error'));

        expect(el.hasAttribute('src')).toBe(false);
        expect(HTMLMediaElement.prototype.load).toHaveBeenCalled();
        expect(player.isFailed('a')).toBe(false);
    });

    describe('playlist', () => {
        let announce;
        const list = ['a', 'b', 'c'].map(track);
        const end = () => audioEl().dispatchEvent(new Event('ended'));

        beforeEach(() => {
            announce = vi.spyOn(vueModule, 'announce').mockImplementation(() => {});
        });

        it('plays the next file of the list when one ends, and announces it once it plays', () => {
            player.toggle(list[0], list);
            end();

            expect(player.state).toMatchObject({ guid: 'b', playing: true, time: 0 });
            expect(audioEl().getAttribute('src')).toBe('/file/download?guid=b');
            expect(announce).not.toHaveBeenCalled();

            audioEl().dispatchEvent(new Event('playing'));
            audioEl().dispatchEvent(new Event('playing'));
            expect(announce).toHaveBeenCalledExactlyOnceWith('Now playing b.mp3');
        });

        it('does not announce a file that failed to start', () => {
            player.toggle(list[0], list);
            end();
            audioEl().dispatchEvent(new Event('error'));
            audioEl().dispatchEvent(new Event('playing'));

            expect(announce).toHaveBeenCalledExactlyOnceWith("b.mp3 can't be played.");
        });

        it('stops after the last file', () => {
            player.toggle(list[2], list);
            end();

            expect(player.state).toMatchObject({ guid: null, playing: false });
            expect(announce).not.toHaveBeenCalled();
        });

        it('skips files that failed before', () => {
            player.toggle(list[1], list);
            audioEl().dispatchEvent(new Event('error'));
            // An error stops the list, it doesn't move on
            expect(player.state.guid).toBeNull();

            player.toggle(list[0], list);
            end();
            expect(player.state.guid).toBe('c');
        });

        it('plays a file started alone once', () => {
            player.toggle(list[0]);
            end();

            expect(player.state.guid).toBeNull();
        });
    });

    it('says aloud that a file can not be played', () => {
        const announce = vi.spyOn(vueModule, 'announce').mockImplementation(() => {});
        player.toggle(track('a'));
        audioEl().dispatchEvent(new Event('error'));

        expect(announce).toHaveBeenCalledExactlyOnceWith("a.mp3 can't be played.");
    });

    it('marks the file failed on an error and refuses to play it again', () => {
        player.toggle(track('a'));
        audioEl().dispatchEvent(new Event('error'));

        expect(player.isFailed('a')).toBe(true);
        expect(player.state.guid).toBeNull();

        player.toggle(track('a'));
        expect(player.state.guid).toBeNull();
    });

    it('does not mark a rejected play() as failed', async () => {
        HTMLMediaElement.prototype.play.mockRejectedValue(new DOMException('blocked', 'NotAllowedError'));
        player.toggle(track('a'));
        await Promise.resolve();

        expect(player.isFailed('a')).toBe(false);
    });

    it('pauses when another media element starts', () => {
        player.toggle(track('a'));
        const video = document.createElement('video');
        document.body.appendChild(video);

        video.dispatchEvent(new Event('play'));

        expect(player.state.playing).toBe(false);
        expect(player.state.guid).toBe('a');
        video.remove();
    });
});

describe('formatDuration', () => {
    it('formats minutes and hours', () => {
        expect(formatDuration(0)).toBe('0:00');
        expect(formatDuration(72.9)).toBe('1:12');
        expect(formatDuration(204)).toBe('3:24');
        expect(formatDuration(3725)).toBe('1:02:05');
    });

    it('is empty for an unknown duration', () => {
        expect(formatDuration(null)).toBe('');
        expect(formatDuration(undefined)).toBe('');
        expect(formatDuration(NaN)).toBe('');
        expect(formatDuration(-1)).toBe('');
    });
});
