<template>
    <svg
        class="progress-frame"
        :class="{ 'is-indeterminate': indeterminate }"
        role="progressbar"
        aria-valuemin="0"
        aria-valuemax="100"
        :aria-valuenow="indeterminate ? null : clamped"
        :aria-label="label"
    >
        <rect class="progress-frame-track" width="100%" height="100%" rx="6" pathLength="100" />
        <rect
            class="progress-frame-bar"
            width="100%"
            height="100%"
            rx="6"
            pathLength="100"
            :style="indeterminate ? null : { strokeDashoffset: 100 - clamped }"
        />
    </svg>
</template>

<script>
/**
 * A progress bar drawn as the outline of its container (`<progress-frame>`) — the upload state
 * of a `TileGrid` tile. Absolutely positioned over the container by its own CSS
 * (`.progress-frame` in `resources/scss/_item-browser.scss`). The frame is drawn in the
 * container's own pixels (the stroke straddles the edge — `.progress-frame` keeps
 * `overflow: visible`), so the stroke width and the 6px corners stay true at any tile size, and
 * `pathLength` makes the dash offset a percentage of the real perimeter.
 *
 * - `value`: 0–100, clamped.
 * - `label`: accessible name.
 * - `indeterminate`: work without a known share is under way (e.g. the server processes an upload): a segment runs
 *   around the container (`.is-indeterminate`; the whole frame stands still with reduced motion), and the
 *   progressbar has no value, as WAI-ARIA has it for an indeterminate one.
 *
 * @since 1.20
 */
export default {
    name: 'ProgressFrame',
    props: {
        value: { type: Number, default: 0 },
        label: { type: String, default: null },
        indeterminate: { type: Boolean, default: false },
    },
    computed: {
        clamped() {
            return Math.min(100, Math.max(0, Math.round(this.value)));
        },
    },
};
</script>
