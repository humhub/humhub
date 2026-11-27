import { describe, expect, it } from 'vitest';
import { justify } from '../../vue/justify.js';

const options = { width: 600, rowHeight: 192, singleHeight: 320, gap: 8, maxRows: 2 };
const widths = (row) => row.items.map((item) => Math.round(item.width));
const rowWidth = (row) => row.items.reduce((sum, item) => sum + item.width, 0) + options.gap * (row.items.length - 1);

describe('justify', () => {
    it('lays out nothing without images or width', () => {
        expect(justify([], options)).toEqual({ rows: [], hidden: 0 });
        expect(justify([1, 1], { ...options, width: 0 })).toEqual({ rows: [], hidden: 0 });
    });

    it('gives a single image its own height, capped by the width', () => {
        const portrait = justify([0.5], options);
        expect(portrait.rows[0].height).toBe(320);
        expect(widths(portrait.rows[0])).toEqual([160]);

        const panorama = justify([4], options);
        expect(panorama.rows[0].height).toBe(150);
        expect(widths(panorama.rows[0])).toEqual([600]);
    });

    it('fills a row to the exact width, closing it where the height comes closest to the target', () => {
        const { rows, hidden } = justify([1, 1, 1, 1], options);

        // Three squares come to 194.7px (target 192), four to 144px: three it is
        expect(rows[0].items).toHaveLength(3);
        expect(rowWidth(rows[0])).toBeCloseTo(600);
        expect(rows[0].height).toBeCloseTo(194.67, 1);
        expect(hidden).toBe(0);
    });

    it('keeps the last row at the target height, left-aligned', () => {
        const { rows } = justify([1, 1, 1, 1], options);

        expect(rows[1].height).toBe(192);
        expect(widths(rows[1])).toEqual([192]);
    });

    it('does not stretch two images that do not fill a row', () => {
        const { rows } = justify([1, 1.5], options);

        expect(rows).toHaveLength(1);
        expect(rows[0].height).toBe(192);
        expect(widths(rows[0])).toEqual([192, 288]);
    });

    it('stops after maxRows and counts the images left', () => {
        const { rows, hidden } = justify(Array(10).fill(1), options);

        expect(rows).toHaveLength(2);
        expect(rows.flatMap((row) => row.items.map((item) => item.index))).toEqual([0, 1, 2, 3, 4, 5]);
        expect(hidden).toBe(4);
    });

    it('treats a wide image that fills a row on its own as a full row', () => {
        const { rows } = justify([5, 1, 1], options);

        expect(rows[0].items.map((item) => item.index)).toEqual([0]);
        expect(rows[0].height).toBe(120);
        expect(rows[1].items.map((item) => item.index)).toEqual([1, 2]);
    });
});
