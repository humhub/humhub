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
 * @since 1.20
 */
import { reactive } from 'vue';
import { announce, i18n } from '@humhub/vue';

/** The current track: `guid` is null while nothing is loaded for playback. `time` and `duration` are seconds. */
export const state = reactive({ guid: null, playing: false, time: 0, duration: null });

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

// The one place that sets the source of a file
function load(element, file) {
    element.src = file.url;
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
    state.guid = null;
    state.playing = false;
    state.time = 0;
    state.duration = null;
}
