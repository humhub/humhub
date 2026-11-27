<template>
    <div
        class="list-group-item file-row"
        :class="{
            'is-uploading': uploading,
            'is-processing': processing,
            'is-invalid': invalid,
            'is-current': current,
            'is-failed': failed,
            'has-subtitles': hasTracks,
        }"
        role="listitem"
        :data-preview-guid="file.guid"
    >
        <i class="ti file-row-icon" :class="'ti-' + (file.icon || 'file')" aria-hidden="true"></i>
        <span class="file-row-text" v-bind="popoverAttributes">
            <span
                v-if="!linked"
                class="file-row-name"
            ><span class="file-row-name-head">{{ name.head }}</span><span class="file-row-name-tail">{{ name.tail }}</span></span>
            <a
                v-else-if="file.viewUrl"
                class="file-row-name"
                :class="{ highlight: !!file.highlight }"
                :href="file.viewUrl"
                data-bs-target="#globalModal"
            ><span class="file-row-name-head">{{ name.head }}</span><span class="file-row-name-tail">{{ name.tail }}</span></a>
            <a
                v-else
                class="file-row-name"
                :class="{ highlight: !!file.highlight }"
                :href="file.url"
                target="_blank"
                rel="noopener"
                v-bind="downloadAttributes"
            ><span class="file-row-name-head">{{ name.head }}</span><span class="file-row-name-tail">{{ name.tail }}</span></a>
            <span class="file-row-meta">({{ meta }})</span>
            <span v-if="invalid" class="invalid-feedback d-block">{{ error }}</span>
            <span v-if="failed" class="file-row-reason">{{ failedLabel }}</span>
        </span>
        <ProgressFrame
            v-if="uploading"
            class="file-row-progress"
            :value="progress"
            :indeterminate="processing"
            :label="uploadingLabel"
        />
        <span v-if="hasTracks" class="file-row-subtitles">
            <select
                v-if="tracks.length > 1"
                ref="track"
                class="form-select form-select-sm file-row-track"
                :aria-label="trackLabel"
                :value="selectedLanguage"
                @change="onTrack"
            >
                <option v-for="track in tracks" :key="track.language" :value="track.language">{{ track.label }}</option>
            </select>
            <button
                ref="captions"
                type="button"
                class="btn btn-ghost btn-sm btn-icon-only file-row-captions"
                :aria-label="captionsLabel"
                :aria-pressed="captionsOn ? 'true' : 'false'"
                :data-bs-title="captionsLabel"
                data-bs-toggle="tooltip"
                @click="onCaptions"
            ><i class="ti ti-badge-cc" aria-hidden="true"></i></button>
        </span>
        <button
            v-if="canPlay"
            ref="play"
            type="button"
            class="btn btn-ghost btn-sm btn-icon-only file-row-play"
            :aria-label="playLabel"
            :data-bs-title="playLabel"
            data-bs-toggle="tooltip"
            @click="onPlay"
        ><i class="ti" :class="playing ? 'ti-player-pause' : 'ti-player-play'" aria-hidden="true"></i></button>
        <button
            v-if="action === 'remove'"
            type="button"
            class="btn btn-ghost btn-ghost-danger btn-sm btn-icon-only file-row-action"
            :aria-label="removeLabel"
            :aria-disabled="busy ? 'true' : null"
            :aria-busy="busy ? 'true' : null"
            :data-bs-title="removeLabel"
            data-bs-toggle="tooltip"
            @click="onRemove"
        ><span v-if="busy" class="spinner-border spinner-border-sm" aria-hidden="true"></span><i v-else class="ti ti-trash" aria-hidden="true"></i></button>
        <a
            v-else-if="linked"
            class="btn btn-ghost btn-sm btn-icon-only file-row-action"
            :href="file.downloadUrl || file.url"
            v-bind="downloadAttributes"
            :aria-label="downloadLabel"
            :data-bs-title="downloadLabel"
            data-bs-toggle="tooltip"
        ><i class="ti ti-download" aria-hidden="true"></i></a>
        <input
            v-if="current"
            ref="seek"
            type="range"
            class="form-range file-row-seek"
            min="0"
            :max="duration || 0"
            step="any"
            :value="time"
            :aria-label="seekLabel"
            :aria-valuetext="seekValueText"
            @input="onSeek"
            @keydown="onSeekKey"
        >
        <div v-if="hasTracks && captionsOn" class="file-row-caption">{{ cue }}</div>
    </div>
</template>

<script>
/**
 * A file row of docs/ui/file-upload.md: icon, the name shortened in the middle, the size, and download or remove as
 * the trailing action. Lists nest it by tag (`<FileRow>`), e.g. `AttachedFiles` of the file module.
 *
 * - `file`: the API's file shape (`file\serializers\FileSerializer::file()`), see `AttachedFiles.vue`. A file picked
 *   in a form has only `fileName` and `size` until the server answers: the generic icon, the name as text.
 * - `action`: `download` (default), or `remove`, which emits `remove`.
 * - `progress`: 0–100 while the file uploads: a `ProgressFrame` runs around the row, the name is no link yet. At 100
 *   the file is sent and the server processes it until the row is done (`.is-processing`): the frame runs as a
 *   segment, without a value.
 * - `error`: the reason a file was refused, shown in the row (`.is-invalid`); it ends the progress.
 * - `busy`: the removal is under way (the file is being deleted): remove shows a spinner and does nothing meanwhile.
 *   `aria-disabled`, not `disabled`, so the button keeps the focus.
 * - `playable`: an audio file: a play/pause button before the download, and, while the file is the current track of
 *   the shared `audioPlayer`, the position as "1:12 / 3:24" and a seek line (`.file-row-seek`) below the name. A file
 *   the browser can't play shows the reason instead and loses the button. `file.duration` (seconds) is shown when known.
 *   While the file has subtitles (`audioPlayer` fetches them on its first play), the current row also shows a CC
 *   toggle (`.file-row-captions`, named "Subtitles", `aria-pressed`), with several languages a select (`.file-row-track`), and the active cue below the
 *   seek line (`.file-row-caption`; no live region: captions would flood a screen reader).
 * - `playlist`: the playable files of the row's list, in order: the next one plays when the file ends. The row emits
 *   `current` when its file becomes the current track (e.g. by following the previous one), so a list can reveal it.
 * - `popoverPlacement`: where the image preview opens on hover (`right`, `top`, …); empty for none.
 *
 * The labels come from `FileModule.base`, remove's from `base`: an island nesting the row preloads these categories.
 *
 * @since 1.20
 */
import { i18n } from '@humhub/vue';
import ProgressFrame from './ProgressFrame.vue';
import { formatDuration } from './audio/formatDuration.js';
import * as audioPlayer from './audio/audioPlayer.js';
import { formatFileSize } from './upload/fileSize.js';

const SEEK_STEP = 5;

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

export default {
    name: 'FileRow',
    components: { ProgressFrame },
    props: {
        file: { type: Object, required: true },
        action: { type: String, default: 'download', validator: (value) => ['download', 'remove'].includes(value) },
        progress: { type: Number, default: null },
        error: { type: String, default: null },
        busy: { type: Boolean, default: false },
        playable: { type: Boolean, default: false },
        playlist: { type: Array, default: null },
        popoverPlacement: { type: String, default: 'right' },
    },
    emits: ['remove', 'current'],
    watch: {
        current(value) {
            if (value) {
                this.$emit('current');
            } else {
                this.keepFocus();
            }
        },
        failed(value) {
            if (value) {
                this.keepFocus();
            }
        },
        // The tooltip of Bootstrap reads its title once, so a created one is told the new label
        playLabel(label) {
            globalThis.bootstrap?.Tooltip?.getInstance(this.$refs.play)?.setContent({ '.tooltip-inner': label });
        },
        playable(value) {
            if (value) {
                audioPlayer.register(this.file.guid);
                this.registeredGuid = this.file.guid;
            } else {
                this.unregister();
            }
        },
    },
    data() {
        // The language last shown, which the CC toggle brings back
        return { lastLanguage: null };
    },
    mounted() {
        if (this.playable) {
            audioPlayer.register(this.file.guid);
            this.registeredGuid = this.file.guid;
        }
    },
    beforeUnmount() {
        this.unregister();
    },
    computed: {
        // The file is the track of the shared player, playing or paused
        current() {
            return this.playable && audioPlayer.state.guid === this.file.guid;
        },
        playing() {
            return this.current && audioPlayer.state.playing;
        },
        // The subtitles of the current file
        tracks() {
            return this.current ? audioPlayer.state.tracks : [];
        },
        hasTracks() {
            return this.tracks.length > 0;
        },
        captionsOn() {
            return audioPlayer.state.trackLanguage !== null;
        },
        cue() {
            return audioPlayer.state.cue;
        },
        // What the select shows while the captions are off: the one a toggle would turn on
        selectedLanguage() {
            return audioPlayer.state.trackLanguage ?? this.lastLanguage ?? this.tracks[0]?.language;
        },
        // A fixed name: `aria-pressed` tells whether they are on
        captionsLabel() {
            return i18n.t('FileModule.base', 'Subtitles');
        },
        trackLabel() {
            return i18n.t('FileModule.base', 'Subtitle language');
        },
        failed() {
            return this.playable && audioPlayer.isFailed(this.file.guid);
        },
        canPlay() {
            return this.playable && this.linked && !this.failed;
        },
        time() {
            return audioPlayer.state.time;
        },
        // The server's value first: it read the whole file, a browser only estimates the length of an MP3 with a
        // variable bitrate and no header
        duration() {
            return this.file.duration || audioPlayer.state.duration || null;
        },
        meta() {
            if (this.current) {
                const duration = formatDuration(this.duration);

                return duration ? `${formatDuration(this.time)} / ${duration}` : formatDuration(this.time);
            }

            const duration = formatDuration(this.file.duration);

            return duration ? `${duration} · ${this.size}` : this.size;
        },
        playLabel() {
            return this.playing
                ? i18n.t('FileModule.base', 'Pause {fileName}', { fileName: this.file.fileName })
                : i18n.t('FileModule.base', 'Play {fileName}', { fileName: this.file.fileName });
        },
        failedLabel() {
            return i18n.t('FileModule.base', "This file can't be played.");
        },
        seekLabel() {
            return i18n.t('FileModule.base', 'Position in {fileName}', { fileName: this.file.fileName });
        },
        seekValueText() {
            const time = formatDuration(this.time);
            const duration = formatDuration(this.duration);

            return duration ? i18n.t('FileModule.base', '{time} of {duration}', { time, duration }) : time;
        },
        invalid() {
            return Boolean(this.error);
        },
        uploading() {
            return this.progress !== null && !this.invalid;
        },
        // Sent, the server's answer is outstanding
        processing() {
            return this.uploading && this.progress >= 100;
        },
        // A file being uploaded, refused or not uploaded yet has nothing to link to
        linked() {
            return !this.uploading && !this.invalid && Boolean(this.file.viewUrl || this.file.url);
        },
        // Head and tail, so the head can be cut off and the extension stays
        name() {
            const fileName = String(this.file.fileName || '');
            const dot = fileName.lastIndexOf('.');

            return dot > 0 ? { head: fileName.slice(0, dot), tail: fileName.slice(dot) } : { head: fileName, tail: '' };
        },
        size() {
            return formatFileSize(this.file.size);
        },
        downloadLabel() {
            return i18n.t('FileModule.base', 'Download {fileName}', { fileName: this.file.fileName });
        },
        removeLabel() {
            return i18n.t('base', 'Remove {label}', { label: this.file.fileName });
        },
        uploadingLabel() {
            return this.processing
                ? i18n.t('FileModule.base', 'Processing {fileName}', { fileName: this.file.fileName })
                : i18n.t('FileModule.base', 'Uploading {fileName}', { fileName: this.file.fileName });
        },
        // What the mobile app reads off a download link (FileDownload::getFileDataAttributes())
        downloadAttributes() {
            return {
                'data-pjax-prevent': '',
                'data-file-download': '',
                'data-file-url': this.file.downloadUrl || this.file.url,
                'data-file-name': this.file.fileName,
                'data-file-mime': this.file.mimeType,
            };
        },
        // The thumbnail popover, initialized by the core `popover` addition (selector `.po`)
        popoverAttributes() {
            if (!this.file.previewUrl || !this.popoverPlacement) {
                return {};
            }

            return {
                class: 'po',
                'data-bs-toggle': 'popover',
                'data-bs-trigger': 'hover',
                'data-bs-placement': this.popoverPlacement,
                'data-bs-container': 'body',
                'data-bs-delay': '100',
                'data-bs-html': 'true',
                'data-bs-content': `<img alt="${escapeHtml(this.file.fileName)}" src="${escapeHtml(this.file.previewUrl)}" />`,
            };
        },
    },
    methods: {
        unregister() {
            if (this.registeredGuid) {
                audioPlayer.release(this.registeredGuid);
                this.registeredGuid = null;
            }
        },
        // The seek line and the play button go away on their own (the track ends, the file fails): when one holds the
        // focus, it moves on to the play button, or to the name link when the file can't be played. Watchers run before
        // the update of the DOM, so the focus is still where it was.
        keepFocus() {
            const active = document.activeElement;
            const lost = ['seek', 'play', 'captions', 'track'].some((ref) => this.$refs[ref] && active === this.$refs[ref]);

            if (!lost) {
                return;
            }

            this.$nextTick(() => {
                const target = this.failed ? 'a.file-row-name' : '.file-row-play';

                this.$el.querySelector(target)?.focus();
            });
        },
        onPlay() {
            audioPlayer.toggle(this.file, this.playlist || [this.file]);
        },
        onCaptions() {
            if (this.captionsOn) {
                this.lastLanguage = audioPlayer.state.trackLanguage;
                audioPlayer.setTrack(null);
            } else {
                audioPlayer.setTrack(this.selectedLanguage);
            }
        },
        onTrack(event) {
            this.lastLanguage = event.target.value;
            audioPlayer.setTrack(event.target.value);
        },
        onSeek(event) {
            audioPlayer.seek(Number(event.target.value));
        },
        // Arrow keys step by seconds instead of the range's tiny native step (left and right swap in right-to-left layouts); Page keys, Home and End stay native
        onSeekKey(event) {
            const rtl = getComputedStyle(event.target).direction === 'rtl';
            const direction = { ArrowLeft: rtl ? 1 : -1, ArrowRight: rtl ? -1 : 1, ArrowUp: 1, ArrowDown: -1 }[event.key];

            if (direction && !event.altKey && !event.ctrlKey && !event.metaKey) {
                event.preventDefault();
                audioPlayer.seek(this.time + direction * SEEK_STEP);
            }
        },
        onRemove() {
            if (!this.busy) {
                this.$emit('remove');
            }
        },
    },
};
</script>
