/**
 * Justified rows for the images of `AttachedFiles.vue` (the principle of Flickr's justified layout):
 *
 * - The images keep their aspect ratio.
 * - A row is filled up to the width and scaled to fit it exactly.
 * - The last row keeps the target height and stays left-aligned.
 *
 * @param {number[]} ratios width / height of each image
 * @param {{width: number, rowHeight: number, singleHeight: number, gap: number, maxRows: number}} options in px
 * @returns {{rows: {height: number, items: {index: number, width: number}[]}[], hidden: number}} `hidden`: the
 *     images after the last row
 */
export function justify(ratios, { width, rowHeight, singleHeight, gap, maxRows }) {
    if (ratios.length === 0 || !(width > 0)) {
        return { rows: [], hidden: 0 };
    }

    // The height at which the images `from`..`to` fill the width
    const fit = (from, to) => {
        let sum = 0;
        for (let i = from; i <= to; i++) {
            sum += ratios[i];
        }
        return (width - gap * (to - from)) / sum;
    };
    const row = (from, to, height) => ({
        height,
        items: ratios.slice(from, to + 1).map((ratio, offset) => ({ index: from + offset, width: ratio * height })),
    });

    if (ratios.length === 1) {
        return { rows: [row(0, 0, Math.min(singleHeight, fit(0, 0)))], hidden: 0 };
    }

    const rows = [];
    let start = 0;
    let next = 0;
    while (next < ratios.length && rows.length < maxRows) {
        const height = fit(start, next);
        if (height > rowHeight) {
            next++; // still room in the row
            continue;
        }

        // Full with image `next`: close the row with or without it, whichever comes closer to the target height
        const without = next > start ? fit(start, next - 1) : Infinity;
        if (without - rowHeight < rowHeight - height) {
            rows.push(row(start, next - 1, without));
            start = next;
        } else {
            rows.push(row(start, next, height));
            start = ++next;
        }
    }
    if (start < next) {
        rows.push(row(start, next - 1, rowHeight)); // the last row, not full
    }

    const shown = rows.reduce((count, { items }) => count + items.length, 0);
    return { rows, hidden: ratios.length - shown };
}
