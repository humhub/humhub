import { beforeEach, describe, expect, it } from 'vitest';

// Stands in for bootstrap.Tooltip: records instances, their config and whether they are shown.
class TooltipStub {
    constructor(el, config) {
        this.el = el;
        this.config = config || {};
        this.shown = false;
        TooltipStub.instances.set(el, this);
    }

    _isShown() {
        return this.shown;
    }

    show() {
        this.shown = true;
    }

    hide() {
        this.shown = false;
    }

    static getInstance(el) {
        return TooltipStub.instances.get(el) || null;
    }

    static getOrCreateInstance(el, config) {
        return TooltipStub.getInstance(el) || new TooltipStub(el, config);
    }
}
TooltipStub.instances = new Map();
globalThis.bootstrap = { Tooltip: TooltipStub };

await import('../../resources/js/humhub/humhub.ui.appearance.js');
await import('../../resources/js/humhub/humhub.ui.tooltip.js');

const root = document.documentElement;
const html = (markup) => {
    document.body.innerHTML = markup;
};
const byId = (id) => document.getElementById(id);
const fire = (el, type) => el.dispatchEvent(new Event(type, { bubbles: true }));
const pressTab = () => document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Tab', bubbles: true }));
const pointerDown = () => document.dispatchEvent(new Event('pointerdown', { bubbles: true }));

describe('ui.tooltip', () => {
    beforeEach(() => {
        TooltipStub.instances.clear();
        root.removeAttribute('data-hh-input-modality');
        html('<button id="b" class="tt" title="Name">x</button><span id="plain">y</span>');
    });

    it('creates and shows the tooltip on hover, with the hover trigger only', () => {
        fire(byId('b'), 'mouseover');
        const tooltip = TooltipStub.getInstance(byId('b'));
        expect(tooltip.shown).toBe(true);
        expect(tooltip.config.trigger).toBe('hover');
    });

    it('ignores elements without a tooltip', () => {
        fire(byId('plain'), 'mouseover');
        fire(byId('plain'), 'focusin');
        expect(TooltipStub.instances.size).toBe(0);
    });

    it('shows the tooltip on focus when the focus moves by keyboard', () => {
        pressTab();
        fire(byId('b'), 'focusin');
        expect(TooltipStub.getInstance(byId('b')).shown).toBe(true);
    });

    it('does not show the tooltip on focus after a click', () => {
        pointerDown();
        fire(byId('b'), 'focusin');
        expect(TooltipStub.getInstance(byId('b'))).toBeNull();
    });

    it('hides the tooltip when the focus leaves', () => {
        pressTab();
        fire(byId('b'), 'focusin');
        fire(byId('b'), 'focusout');
        expect(TooltipStub.getInstance(byId('b')).shown).toBe(false);
    });

    it('hides every shown tooltip on Escape and keeps the key from closing more', () => {
        fire(byId('b'), 'mouseover');
        let reachedBody = false;
        document.body.addEventListener('keydown', () => { reachedBody = true; }, { once: true });
        byId('b').dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true, cancelable: true }));
        expect(TooltipStub.getInstance(byId('b')).shown).toBe(false);
        expect(reachedBody).toBe(false);
    });

    it('lets Escape through when no tooltip is shown', () => {
        let reachedBody = false;
        document.body.addEventListener('keydown', () => { reachedBody = true; }, { once: true });
        byId('b').dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true }));
        expect(reachedBody).toBe(true);
    });

    it('hides the tooltip on a click, so Escape passes through afterwards', () => {
        fire(byId('b'), 'mouseover');
        byId('b').dispatchEvent(new MouseEvent('click', { bubbles: true }));
        expect(TooltipStub.getInstance(byId('b')).shown).toBe(false);
        let reachedBody = false;
        document.body.addEventListener('keydown', () => { reachedBody = true; }, { once: true });
        byId('b').dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true }));
        expect(reachedBody).toBe(true);
    });
});
