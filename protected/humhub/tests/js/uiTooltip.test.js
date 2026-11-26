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

    // As Bootstrap: a cancelable show event on the trigger first
    show() {
        const event = new Event('show.bs.tooltip', { bubbles: true, cancelable: true });
        this.el.dispatchEvent(event);
        if (!event.defaultPrevented) {
            this.shown = true;
        }
    }

    // As Bootstrap: a cancelable hide event on the trigger first
    hide() {
        const event = new Event('hide.bs.tooltip', { bubbles: true, cancelable: true });
        this.el.dispatchEvent(event);
        if (!event.defaultPrevented) {
            this.shown = false;
        }
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
        // Time to move the pointer onto the tooltip
        expect(tooltip.config.delay).toEqual({ show: 0, hide: 150 });
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

    it('removes orphaned tooltip elements on a click and keeps referenced ones', () => {
        document.body.insertAdjacentHTML('beforeend', '<div class="tooltip show" id="tooltip-orphan">o</div>'
            + '<div class="tooltip show" id="tooltip-live">l</div><button id="ref" aria-describedby="tooltip-live">r</button>');
        document.body.dispatchEvent(new MouseEvent('click', { bubbles: true }));
        expect(byId('tooltip-orphan')).toBeNull();
        expect(byId('tooltip-live')).not.toBeNull();
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

    describe('hoverable (WCAG 1.4.13)', () => {
        // jsdom keeps the hover state of the last mouseover, as a browser does of the pointer
        const showWithTip = () => {
            fire(byId('b'), 'mouseover');
            const tooltip = TooltipStub.getInstance(byId('b'));
            tooltip.tip = document.createElement('div');
            document.body.appendChild(tooltip.tip);
            return tooltip;
        };

        it('keeps the tooltip while the pointer is on it, and hides it when the pointer leaves it', () => {
            const tooltip = showWithTip();

            // Bootstrap hides it when the pointer left the trigger for the tooltip
            fire(tooltip.tip, 'mouseover');
            tooltip.hide();
            expect(tooltip.shown).toBe(true);

            fire(byId('plain'), 'mouseover');
            tooltip.tip.dispatchEvent(new Event('mouseleave'));
            expect(tooltip.shown).toBe(false);
        });

        it('keeps it when the pointer goes back from the tooltip to the trigger', () => {
            const tooltip = showWithTip();
            fire(tooltip.tip, 'mouseover');
            tooltip.hide();

            fire(byId('b'), 'mouseover');
            tooltip.tip.dispatchEvent(new Event('mouseleave'));
            expect(tooltip.shown).toBe(true);
        });

        it('hides the tooltip when the pointer is elsewhere', () => {
            const tooltip = showWithTip();
            fire(byId('plain'), 'mouseover');

            tooltip.hide();
            expect(tooltip.shown).toBe(false);
        });

        it('still hides it on Escape while the pointer is on it', () => {
            const tooltip = showWithTip();
            fire(tooltip.tip, 'mouseover');

            byId('b').dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true, cancelable: true }));
            expect(tooltip.shown).toBe(false);
        });
    });

    describe('a trigger inside a link or button', () => {
        beforeEach(() => {
            html('<a id="link" href="/perma"><time id="time" class="tt" title="Created at: 21 Nov">21 Nov</time></a>'
                + '<a id="two" href="/x"><span class="tt" title="A">a</span><span class="tt" title="B">b</span></a>');
        });

        it('shows its tooltip when the link gets the keyboard focus, and hides it when the focus leaves', () => {
            pressTab();
            fire(byId('link'), 'focusin');
            expect(TooltipStub.getInstance(byId('time')).shown).toBe(true);

            fire(byId('link'), 'focusout');
            expect(TooltipStub.getInstance(byId('time')).shown).toBe(false);
        });

        it('shows none for a link with several', () => {
            pressTab();
            fire(byId('two'), 'focusin');
            expect(TooltipStub.instances.size).toBe(0);
        });
    });

    describe('a text cut off (data-tooltip="overflow", WCAG 1.4.12)', () => {
        // jsdom lays nothing out: the sizes say whether the text is cut off
        const size = (el, scrollWidth, clientWidth) => {
            Object.defineProperty(el, 'scrollWidth', { value: scrollWidth, configurable: true });
            Object.defineProperty(el, 'clientWidth', { value: clientWidth, configurable: true });
        };

        beforeEach(() => {
            html('<a id="toggle" href="#"><div id="name" data-tooltip="overflow"><strong>Maximiliane Wolkenstein</strong><br>'
                + '<span>System Administration</span></div><span id="dot" class="tt" title="Online"></span></a>');
        });

        it('shows its full text as tooltip while it is cut off', () => {
            size(byId('name'), 274, 200);
            fire(byId('name'), 'mouseover');

            const tooltip = TooltipStub.getInstance(byId('name'));
            expect(tooltip.shown).toBe(true);
            // The text itself; a browser's innerText also turns the line break into a space, jsdom has none
            expect(tooltip.config.title.call(byId('name'))).toBe('Maximiliane WolkensteinSystem Administration');
        });

        it('shows none when the text fits', () => {
            size(byId('name'), 150, 200);
            fire(byId('name'), 'mouseover');

            expect(TooltipStub.getInstance(byId('name')).shown).toBe(false);
        });

        it('wins over the other triggers of a focused link while it is cut off', () => {
            size(byId('name'), 274, 200);
            pressTab();
            fire(byId('toggle'), 'focusin');

            expect(TooltipStub.getInstance(byId('name')).shown).toBe(true);
            expect(TooltipStub.getInstance(byId('dot'))).toBeNull();
        });

        it('leaves a focused link without tooltip when the text fits and it has other triggers', () => {
            size(byId('name'), 150, 200);
            pressTab();
            fire(byId('toggle'), 'focusin');

            expect(TooltipStub.instances.size).toBe(0);
        });

        it('leaves the other tooltips when the text fits and shows none', () => {
            fire(byId('dot'), 'mouseover');
            size(byId('name'), 150, 200);
            fire(byId('name'), 'mouseover');

            expect(TooltipStub.getInstance(byId('dot')).shown).toBe(true);
        });
    });

    describe('one at a time', () => {
        beforeEach(() => {
            html('<button id="b" class="tt" title="Name">x</button><button id="c" class="tt" title="Other">y</button>');
        });

        it('closes the others at once, without fading out, when a tooltip shows', () => {
            fire(byId('b'), 'mouseover');
            const first = TooltipStub.getInstance(byId('b'));
            first.tip = document.createElement('div');
            first.tip.className = 'tooltip fade show';

            // Before its hide delay is over
            fire(byId('c'), 'mouseover');

            expect(first.shown).toBe(false);
            expect(first.tip.classList.contains('fade')).toBe(false);
            expect(TooltipStub.getInstance(byId('c')).shown).toBe(true);
        });

        it('closes the tooltip of the pointer when one shows on keyboard focus', () => {
            fire(byId('b'), 'mouseover');
            pressTab();
            fire(byId('c'), 'focusin');

            expect(TooltipStub.getInstance(byId('b')).shown).toBe(false);
            expect(TooltipStub.getInstance(byId('c')).shown).toBe(true);
        });
    });
});
