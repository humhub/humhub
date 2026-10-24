<template>
    <svg
        class="c-progress-frame"
        role="progressbar"
        aria-valuemin="0"
        aria-valuemax="100"
        :aria-valuenow="clamped"
        :aria-label="label"
    >
        <rect class="c-progress-frame__track" width="100%" height="100%" rx="6" pathLength="100" />
        <rect
            class="c-progress-frame__bar"
            width="100%"
            height="100%"
            rx="6"
            pathLength="100"
            :style="{ strokeDashoffset: 100 - clamped }"
        />
    </svg>
</template>

<script>
/**
 * A progress bar drawn as the outline of its container (`<progress-frame>`) — the upload state
 * of a `TileGrid` tile. Absolutely positioned over the container by its own CSS
 * (`.c-progress-frame` in `resources/scss/_item-browser.scss`). The frame is drawn in the
 * container's own pixels (the stroke straddles the edge — `.c-progress-frame` keeps
 * `overflow: visible`), so the stroke width and the 6px corners stay true at any tile size, and
 * `pathLength` makes the dash offset a percentage of the real perimeter.
 *
 * - `value`: 0–100, clamped.
 * - `label`: accessible name.
 *
 * @since 1.20
 */
export default {
    name: 'ProgressFrame',
    props: {
        value: { type: Number, default: 0 },
        label: { type: String, default: null },
    },
    computed: {
        clamped() {
            return Math.min(100, Math.max(0, Math.round(this.value)));
        },
    },
};
</script>
