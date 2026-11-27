/**
 * The one audio player of the page: attached audio files play through a single lazily created `Audio` element, so
 * only one file sounds at a time. Core components import it (`FileRow`); a module bundle must not, or it would get a
 * second player. Not registered as a component.
 *
 * Islands are unmounted when their element leaves the document (also on Pjax navigation, see `humhub.vue.js`), which
 * releases the rows and stops the playback of a file no row shows any more.
 *
 * A file started from a list (its playlist) is followed by the next file of that list when it ends; the change is
 * announced, as it happens without the user's doing. After the last file, or a file that fails, the player stops.
 *
 * Subtitles: on the first play of a file the player fetches its variants (`GET /api/v2/file/{id}/variants`, once per
 * file) and adds a `<track>` per variant named `subtitles-<language>.vtt` or `captions-<language>.vtt`. `state.tracks` lists them, `state.trackLanguage` is the shown one
 * (by default the page language's, else null: off), `state.cue` the text of the active cue as plain text. Reset when
 * the file stops or another one starts. The `humhub:file:variants-changed` event on `document` (`detail: {fileId}`, fired
 * by the subtitle manager) drops the list of that file and, if it is loaded, fetches its tracks again.
 *
 * @since 1.20
 */
import { reactive } from 'vue';
import { announce, apiUrl, client, i18n } from '@humhub/vue';
import { languageLabel, subtitleTracks } from './subtitleVariants.js';

/**
 * The current track: `guid` is null while nothing is loaded for playback. `time` and `duration` are seconds. `tracks` are
 * the subtitles of the file (`{language, label, kind}`, the label is the language's name in the page language), `trackLanguage` the shown one (null: none), `cue` the text of
 * the active cue.
 */
export const state = reactive({ guid: null, playing: false, time: 0, duration: null, tracks: [], trackLanguage: null, cue: '' });

// Guids of files the browser can't play
const failed = reactive({});
// How many mounted rows show a guid
const rows = new Map();
// The files of the list the current file was started from, in their order
let playlist = [];
let audio = null;
// The file being played, and whether its length is known from the server (then the browser's estimate is ignored)
let current = null;
let durationKnown = false;
// The name of a file the player moved on to by itself: announced once it really plays
let pendingAnnouncement = null;
// The subtitles variants by file id: the request (once per file), and the `<track>` elements of the current file by language
const variants = new Map();
let trackElements = new Map();

const MEDIA_EVENTS = {
    play: syncPlaying,
    pause: syncPlaying,
    playing: onPlaying,
    timeupdate: () => {
        state.time = audio.currentTime;
    },
    durationchange: onDuration,
    loadedmetadata: onDuration,
    ended: playNext,
    error: onError,
};

function syncPlaying() {
    state.playing = state.guid !== null && !audio.paused;
}

function onPlaying() {
    syncPlaying();

    if (pendingAnnouncement !== null) {
        announce(i18n.t('FileModule.base', 'Now playing {fileName}', { fileName: pendingAnnouncement }));
        pendingAnnouncement = null;
    }
}

function onDuration() {
    if (!durationKnown && Number.isFinite(audio.duration)) {
        state.duration = audio.duration;
    }
}

// The source can't be loaded or decoded. This, not a rejected `play()`, marks a file as failed.
function onError() {
    // Nothing is loaded: the error of a source that was emptied by `stop()`
    if (state.guid === null) {
        return;
    }

    failed[state.guid] = true;
    // Away from the focus, so said aloud: the row shows the reason
    announce(i18n.t('FileModule.base', "{fileName} can't be played.", { fileName: current.fileName }));
    stop();
}

// Another media element (e.g. a video of the media viewer) starts: yield to it
function onOtherPlay(event) {
    if (audio && event.target !== audio && !audio.paused) {
        audio.pause();
    }
}

// The next playable file of the playlist, if any
function playNext() {
    const index = playlist.findIndex((file) => file.guid === state.guid);
    const next = index === -1 ? null : playlist.slice(index + 1).find((file) => !failed[file.guid]);

    if (!next) {
        stop();
        return;
    }

    play(audio, next);
    pendingAnnouncement = next.fileName;
}

function stop() {
    state.guid = null;
    current = null;
    pendingAnnouncement = null;
    durationKnown = false;
    clearTracks();
    if (audio) {
        audio.pause();
        // The browser stops loading the file; the emptied source is no error (no guid)
        audio.removeAttribute('src');
        audio.load();
    }
    state.playing = false;
    state.time = 0;
    state.duration = null;
}

function ensureAudio() {
    if (!audio) {
        // In the document (hidden), so `<track>` children load reliably
        audio = document.createElement('audio');
        audio.hidden = true;
        audio.preload = 'none';
        document.body.appendChild(audio);
        Object.entries(MEDIA_EVENTS).forEach(([name, handler]) => audio.addEventListener(name, handler));
        // Captured: `play` doesn't bubble
        document.addEventListener('play', onOtherPlay, true);
    }

    return audio;
}

/**
 * Pauses or resumes the current file, or stops it and plays another one.
 *
 * @param {{guid: string, url: string, fileName: string, duration?: number|null}} file
 * @param {Object[]} [list] the files of the list `file` belongs to, in order: the next one follows when a file ends
 */
export function toggle(file, list = [file]) {
    if (failed[file.guid]) {
        return;
    }

    const element = ensureAudio();

    if (state.guid === file.guid) {
        if (element.paused) {
            start(element);
        } else {
            element.pause();
        }
        return;
    }

    playlist = list;
    play(element, file);
}

function play(element, file) {
    element.pause();
    load(element, file);
    state.guid = file.guid;
    current = file;
    pendingAnnouncement = null;
    state.time = 0;
    state.duration = file.duration || null;
    durationKnown = state.duration !== null;
    start(element);
}

// The one place that sets the source of a file: its subtitles come with it, once they are known
function load(element, file) {
    clearTracks();
    element.src = file.url;
    addTracks(element, file);
}

// The variants of a file, requested once per file. A failed request is forgotten, the next play asks again.
function fetchVariants(file) {
    if (!variants.has(file.id)) {
        const request = client
            .get(apiUrl(`file/${file.id}/variants`))
            .then((response) => subtitleTracks(response && response.results))
            .catch(() => {
                variants.delete(file.id);
                return [];
            });

        variants.set(file.id, request);
    }

    return variants.get(file.id);
}

// The subtitles of a file changed (the manager saved or deleted one): the list is requested again, and the file that is
// loaded now gets its tracks anew
document.addEventListener('humhub:file:variants-changed', (event) => {
    const fileId = Number(event.detail?.fileId);

    variants.forEach((request, id) => Number(id) === fileId && variants.delete(id));
    if (audio && current && Number(current.id) === fileId) {
        clearTracks();
        addTracks(audio, current);
    }
});

// The page language's primary subtag ("pt-BR" and "pt" are both "pt")
const primaryLanguage = (language) => String(language || '').toLowerCase().split(/[-_]/)[0];

// Plain text of a cue: the tags go and the escapes of WebVTT are decoded; nothing here is ever HTML
function plainText(text) {
    const entities = { '&amp;': '&', '&lt;': '<', '&gt;': '>', '&nbsp;': ' ', '&lrm;': '\u200e', '&rlm;': '\u200f' };

    return String(text ?? '')
        .replace(/<[^>]*>/g, '')
        .replace(/&(amp|lt|gt|nbsp|lrm|rlm);/g, (entity) => entities[entity]);
}

function updateCue(language, track) {
    if (state.trackLanguage !== language) {
        return;
    }

    state.cue = Array.from(track.activeCues || [], (cue) => plainText(cue.text)).join('\n');
}

function clearTracks() {
    trackElements.forEach((element) => element.remove());
    trackElements = new Map();
    state.tracks = [];
    state.trackLanguage = null;
    state.cue = '';
}

// The variants of the current file become its `<track>` children, once the request is answered
function addTracks(element, file) {
    if (!file.id) {
        return;
    }

    fetchVariants(file).then((list) => {
        // The player has moved on, or the tracks are there already
        if (current !== file || trackElements.size > 0 || list.length === 0) {
            return;
        }

        const preferred = list.find((variant) => primaryLanguage(variant.language) === primaryLanguage(document.documentElement.lang));

        list.forEach((variant) => {
            const track = document.createElement('track');

            track.kind = variant.kind === 'captions' ? 'captions' : 'subtitles';
            track.srclang = variant.language;
            track.label = languageLabel(variant.language);
            track.src = variant.url;
            element.appendChild(track);
            track.track?.addEventListener('cuechange', () => updateCue(variant.language, track.track));
            trackElements.set(variant.language, track);
        });

        state.tracks = list.map(({ language, kind }) => ({ language, label: languageLabel(language), kind }));
        setTrack(preferred ? preferred.language : null);
    });
}

// A rejected `play()` is no failed file: it is an autoplay block (NotAllowedError) or a pause/new source while
// starting (AbortError), and a broken source fires `error`, which marks it. So the row only shows "paused" again.
function start(element) {
    const promise = element.play();

    if (promise && typeof promise.catch === 'function') {
        promise.catch(syncPlaying);
    }
}

/**
 * Shows the subtitles of a language, or none.
 *
 * @param {?string} language one of `state.tracks`, null for none
 */
export function setTrack(language) {
    const shown = language !== null && trackElements.has(language) ? language : null;

    state.trackLanguage = shown;
    state.cue = '';
    trackElements.forEach((element, key) => {
        if (element.track) {
            // `hidden`: the cues are loaded and fire `cuechange`, the page shows them itself
            element.track.mode = key === shown ? 'hidden' : 'disabled';
        }
    });

    if (shown !== null) {
        updateCue(shown, trackElements.get(shown).track || {});
    }
}

/**
 * @param {number} seconds clamped to the file
 */
export function seek(seconds) {
    if (!audio || state.guid === null) {
        return;
    }

    const max = state.duration ?? Infinity;
    const time = Math.min(Math.max(Number(seconds) || 0, 0), max);

    audio.currentTime = time;
    state.time = time;
}

/** A row showing the file mounted. */
export function register(guid) {
    rows.set(guid, (rows.get(guid) || 0) + 1);
}

/** A row showing the file unmounted: the file stops when it was the current one and no row shows it any more. */
export function release(guid) {
    const count = (rows.get(guid) || 0) - 1;

    if (count > 0) {
        rows.set(guid, count);
        return;
    }

    rows.delete(guid);

    if (state.guid === guid) {
        stop();
    }
}

export function isFailed(guid) {
    return failed[guid] === true;
}

/** For tests only: back to the state before the first use. */
export function _reset() {
    if (audio) {
        audio.pause();
        Object.entries(MEDIA_EVENTS).forEach(([name, handler]) => audio.removeEventListener(name, handler));
        document.removeEventListener('play', onOtherPlay, true);
        audio.remove();
        audio = null;
    }
    rows.clear();
    playlist = [];
    current = null;
    durationKnown = false;
    pendingAnnouncement = null;
    Object.keys(failed).forEach((guid) => delete failed[guid]);
    variants.clear();
    clearTracks();
    state.guid = null;
    state.playing = false;
    state.time = 0;
    state.duration = null;
}
