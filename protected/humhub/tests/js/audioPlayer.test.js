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

describe('audioPlayer subtitles', () => {
    const variant = (language, kind = 'subtitles') => ({
        name: `subtitles-${language}.vtt`, type: 'subtitles', language, label: language.toUpperCase(), kind, url: `/v/${language}.vtt`,
    });
    const file = (guid) => ({ ...track(guid), id: guid === 'a' ? 5 : 6 });
    const flush = () => new Promise((resolve) => setTimeout(resolve, 0));
    const { lang } = document.documentElement;
    let get;

    // jsdom has no text tracks: every `<track>` gets a stand-in with a mode and cues
    const fakeTracks = new WeakMap();
    const fake = (el) => {
        if (!fakeTracks.has(el)) {
            const textTrack = new EventTarget();
            Object.assign(textTrack, { mode: 'disabled', activeCues: [] });
            fakeTracks.set(el, textTrack);
        }
        return fakeTracks.get(el);
    };
    const trackEls = () => [...audioEl().querySelectorAll('track')];

    beforeEach(() => {
        vi.spyOn(HTMLMediaElement.prototype, 'play').mockImplementation(() => Promise.resolve());
        vi.spyOn(HTMLMediaElement.prototype, 'pause').mockImplementation(() => {});
        vi.spyOn(HTMLMediaElement.prototype, 'load').mockImplementation(() => {});
        Object.defineProperty(HTMLElement.prototype, 'track', { configurable: true, get() { return fake(this); } });
        get = vi.fn(() => Promise.resolve({ results: [variant('de'), variant('en', 'captions')] }));
        globalThis.humhubStubs.client.get = get;
    });

    afterEach(() => {
        document.documentElement.lang = lang;
        delete HTMLElement.prototype.track;
    });

    it('fetches the variants on the first play, once per file, and adds the tracks', async () => {
        player.toggle(file('a'));
        await flush();

        expect(get).toHaveBeenCalledTimes(1);
        expect(get.mock.calls[0][0]).toMatch(/\/api\/v2\/file\/5\/variants$/);
        expect(trackEls().map((el) => [el.getAttribute('kind'), el.getAttribute('srclang'), el.getAttribute('label'), el.getAttribute('src')]))
            .toEqual([['subtitles', 'de', 'DE', '/v/de.vtt'], ['captions', 'en', 'EN', '/v/en.vtt']]);
        expect(player.state.tracks).toEqual([
            { language: 'de', label: 'DE', kind: 'subtitles' },
            { language: 'en', label: 'EN', kind: 'captions' },
        ]);

        // Played again after another file: from memory
        player.toggle(file('b'));
        await flush();
        player.toggle(file('a'));
        await flush();
        expect(get.mock.calls.filter(([url]) => url.endsWith('/file/5/variants'))).toHaveLength(1);
        expect(trackEls()).toHaveLength(2);
    });

    it('shows the page language, the others stay disabled; none when no language matches', async () => {
        document.documentElement.lang = 'en-GB';
        player.toggle(file('a'));
        await flush();

        expect(player.state.trackLanguage).toBe('en');
        expect(trackEls().map((el) => el.track.mode)).toEqual(['disabled', 'hidden']);

        player.toggle(file('b'));
        document.documentElement.lang = 'fr';
        await flush();
        expect(player.state.trackLanguage).toBeNull();
        expect(trackEls().map((el) => el.track.mode)).toEqual(['disabled', 'disabled']);
    });

    it('switches the track with setTrack and off with null', async () => {
        document.documentElement.lang = 'de';
        player.toggle(file('a'));
        await flush();

        player.setTrack('en');
        expect(player.state.trackLanguage).toBe('en');
        expect(trackEls().map((el) => el.track.mode)).toEqual(['disabled', 'hidden']);

        player.setTrack('xx');
        expect(player.state.trackLanguage).toBeNull();

        player.setTrack('de');
        player.setTrack(null);
        expect(player.state.trackLanguage).toBeNull();
        expect(trackEls().map((el) => el.track.mode)).toEqual(['disabled', 'disabled']);
    });

    it('takes the active cue as plain text, never HTML', async () => {
        document.documentElement.lang = 'de';
        player.toggle(file('a'));
        await flush();
        const [de, en] = trackEls().map((el) => el.track);

        de.activeCues = [{ text: '<v Anna><b>Hi</b> &lt;you&gt; &amp; me</v>' }, { text: '<i>Second</i>' }];
        de.dispatchEvent(new Event('cuechange'));
        expect(player.state.cue).toBe('Hi <you> & me\nSecond');

        // A cue of a track that is not shown is ignored
        en.activeCues = [{ text: 'Other' }];
        en.dispatchEvent(new Event('cuechange'));
        expect(player.state.cue).toBe('Hi <you> & me\nSecond');

        de.activeCues = [];
        de.dispatchEvent(new Event('cuechange'));
        expect(player.state.cue).toBe('');
    });

    it('resets on stop and on the next file', async () => {
        document.documentElement.lang = 'de';
        player.toggle(file('a'));
        await flush();
        trackEls()[0].track.activeCues = [{ text: 'Hi' }];
        trackEls()[0].track.dispatchEvent(new Event('cuechange'));
        expect(player.state.cue).toBe('Hi');

        get.mockImplementation(() => new Promise(() => {}));
        player.toggle(file('b'));
        expect(trackEls()).toHaveLength(0);
        expect(player.state).toMatchObject({ tracks: [], trackLanguage: null, cue: '' });

        player.release('b');
        player.register('b');
        player.toggle(file('b'));
        player.release('b');
        expect(player.state.guid).toBeNull();
        expect(player.state.tracks).toEqual([]);
    });

    it('ignores the answer for a file the player has left', async () => {
        let answer;
        get.mockImplementation(() => new Promise((resolve) => { answer = resolve; }));
        player.toggle(file('a'));
        get.mockImplementation(() => new Promise(() => {}));
        player.toggle(file('b'));
        answer({ results: [variant('de')] });
        await flush();

        expect(trackEls()).toHaveLength(0);
        expect(player.state.tracks).toEqual([]);
    });

    it('plays without subtitles when the request fails, and asks again next time', async () => {
        get.mockImplementation(() => Promise.reject(new Error('boom')));
        player.toggle(file('a'));
        await flush();

        expect(player.state).toMatchObject({ guid: 'a', tracks: [], trackLanguage: null });
        expect(trackEls()).toHaveLength(0);

        get.mockImplementation(() => Promise.resolve({ results: [variant('de')] }));
        player.toggle(file('b'));
        player.toggle(file('a'));
        await flush();
        expect(trackEls()).toHaveLength(1);
    });

    it('forgets the variants of a file on humhub:file:variants-changed and reloads the tracks of the loaded one', async () => {
        player.toggle(file('a'));
        await flush();
        expect(trackEls()).toHaveLength(2);

        get.mockImplementation(() => Promise.resolve({ results: [variant('fr')] }));
        document.dispatchEvent(new CustomEvent('humhub:file:variants-changed', { detail: { fileId: 5 } }));
        await flush();

        expect(get).toHaveBeenCalledTimes(2);
        expect(trackEls().map((el) => el.getAttribute('srclang'))).toEqual(['fr']);

        // Another file's change leaves the loaded one and its list alone
        document.dispatchEvent(new CustomEvent('humhub:file:variants-changed', { detail: { fileId: 6 } }));
        await flush();
        expect(get).toHaveBeenCalledTimes(2);
        expect(trackEls()).toHaveLength(1);

        // A file that is not loaded asks again on its next play
        player.toggle(file('b'));
        await flush();
        expect(get).toHaveBeenCalledTimes(3);
        document.dispatchEvent(new CustomEvent('humhub:file:variants-changed', { detail: { fileId: 6 } }));
        player.toggle(file('a'));
        await flush();
        player.toggle(file('b'));
        await flush();
        expect(get.mock.calls.filter(([url]) => url.endsWith('/file/6/variants'))).toHaveLength(2);
    });

    it('asks for nothing without a file id', async () => {
        player.toggle(track('a'));
        await flush();

        expect(get).not.toHaveBeenCalled();
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
