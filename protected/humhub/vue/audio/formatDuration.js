/**
 * The duration of a media file as a row shows it. Free of imports, so the bundle of any module can include it
 * without pulling in the audio player.
 *
 * @since 1.20
 */

/**
 * @param {number|null|undefined} seconds
 * @returns {string} "3:24", "1:02:05" from an hour on; empty for an unknown (null, NaN, negative) duration
 */
export function formatDuration(seconds) {
    const total = Number(seconds);

    if (seconds === null || seconds === undefined || !Number.isFinite(total) || total < 0) {
        return '';
    }

    const whole = Math.floor(total);
    const hours = Math.floor(whole / 3600);
    const minutes = Math.floor((whole % 3600) / 60);
    const secs = String(whole % 60).padStart(2, '0');

    return hours > 0 ? `${hours}:${String(minutes).padStart(2, '0')}:${secs}` : `${minutes}:${secs}`;
}
