import { afterEach, describe, expect, it } from 'vitest';
import { DRAWER_GAP, positionDrawer } from '../../vue/picker/drawerPosition.js';

const rect = (top, height, left = 10, width = 200) => ({ top, bottom: top + height, left, right: left + width, width, height });

const setup = ({ field, drawerHeight, viewport = 800 }) => {
    const fieldEl = document.createElement('div');
    const drawer = document.createElement('ul');
    fieldEl.getBoundingClientRect = () => field;
    Object.defineProperty(drawer, 'scrollHeight', { configurable: true, value: drawerHeight });
    Object.defineProperty(window, 'innerHeight', { configurable: true, value: viewport });
    return { fieldEl, drawer };
};

describe('positionDrawer', () => {
    afterEach(() => {
        delete window.visualViewport;
    });

    it('places the drawer fixed below the field, at its width', () => {
        const { fieldEl, drawer } = setup({ field: rect(100, 38), drawerHeight: 400 });

        const result = positionDrawer(fieldEl, drawer, 384);

        expect(result.dropUp).toBe(false);
        expect(drawer.style.position).toBe('fixed');
        expect(drawer.style.top).toBe(`${138 + DRAWER_GAP}px`);
        expect(drawer.style.left).toBe('10px');
        expect(drawer.style.width).toBe('200px');
        expect(drawer.style.maxHeight).toBe('384px');
    });

    it('opens above when there is less room below than its height and more above', () => {
        const { fieldEl, drawer } = setup({ field: rect(700, 38), drawerHeight: 300 });

        const result = positionDrawer(fieldEl, drawer, 384);

        expect(result.dropUp).toBe(true);
        expect(drawer.style.top).toBe(`${700 - DRAWER_GAP - 300}px`);
        expect(drawer.style.maxHeight).toBe('300px');
    });

    it('limits its height to the room it has', () => {
        const { fieldEl, drawer } = setup({ field: rect(300, 38), drawerHeight: 600, viewport: 600 });

        const result = positionDrawer(fieldEl, drawer, 384);

        expect(result.dropUp).toBe(true);
        expect(drawer.style.maxHeight).toBe(`${300 - DRAWER_GAP}px`);
    });

    it('counts its border into the height, so a short list does not scroll', () => {
        const { fieldEl, drawer } = setup({ field: rect(100, 38), drawerHeight: 50 });
        Object.defineProperty(drawer, 'offsetHeight', { configurable: true, value: 52 });
        Object.defineProperty(drawer, 'clientHeight', { configurable: true, value: 50 });

        positionDrawer(fieldEl, drawer, 384);

        expect(drawer.style.maxHeight).toBe('52px');
    });

    it('measures the field once the drawer is out of the page flow', () => {
        // In the flow the drawer can lengthen the page: a scrollbar shifts the field until it is fixed
        const { fieldEl, drawer } = setup({ field: rect(100, 38), drawerHeight: 200 });
        fieldEl.getBoundingClientRect = () => (drawer.style.position === 'fixed' ? rect(100, 38, 18, 400) : rect(100, 38, 10, 385));

        positionDrawer(fieldEl, drawer, 384);

        expect(drawer.style.left).toBe('18px');
        expect(drawer.style.width).toBe('400px');
    });

    it('counts the visual viewport (on-screen keyboard)', () => {
        const { fieldEl, drawer } = setup({ field: rect(300, 38), drawerHeight: 200, viewport: 800 });
        window.visualViewport = { offsetTop: 0, height: 400 };

        expect(positionDrawer(fieldEl, drawer, 384).dropUp).toBe(true);
    });
});
