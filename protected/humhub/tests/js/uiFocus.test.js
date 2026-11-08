import { beforeEach, describe, expect, it } from 'vitest';

// jsdom has no layout: an element counts as shown unless it or an ancestor is `hidden` or has an
// inline `display: none` (the module prefers checkVisibility() over getClientRects()).
Element.prototype.checkVisibility = function () {
    for (let el = this; el; el = el.parentElement) {
        if (el.hidden || el.style.display === 'none') {
            return false;
        }
    }
    return true;
};

await import('../../resources/js/humhub/humhub.ui.focus.js');
const focus = globalThis.humhub.modules['ui.focus'];

const html = (markup) => {
    document.body.innerHTML = markup;
};
const byId = (id) => document.getElementById(id);
const loseFocus = () => {
    if (document.activeElement && document.activeElement !== document.body) {
        document.activeElement.blur();
    }
};

describe('ui.focus', () => {
    beforeEach(() => {
        loseFocus();
        document.body.innerHTML = '';
    });

    describe('focus()', () => {
        it('focuses a control', () => {
            html('<button id="b">x</button>');
            expect(focus.focus(byId('b'))).toBe(true);
            expect(document.activeElement.id).toBe('b');
        });

        it('makes a plain element a programmatic focus target', () => {
            html('<div id="d">x</div>');
            expect(focus.focus(byId('d'))).toBe(true);
            expect(byId('d').getAttribute('tabindex')).toBe('-1');
            expect(document.activeElement.id).toBe('d');
        });

        it('accepts a jQuery object', () => {
            html('<button id="b">x</button>');
            expect(focus.focus(jQuery('#b'))).toBe(true);
            expect(document.activeElement.id).toBe('b');
        });

        it('refuses a hidden, disabled or detached element', () => {
            html('<div hidden><button id="h">x</button></div><button id="d" disabled>x</button>');
            expect(focus.focus(byId('h'))).toBe(false);
            expect(focus.focus(byId('d'))).toBe(false);
            expect(focus.focus(document.createElement('button'))).toBe(false);
            expect(focus.focus(null)).toBe(false);
            expect(document.activeElement).toBe(document.body);
        });
    });

    describe('focusInto()', () => {
        it('focuses the first shown control inside', () => {
            html('<div id="e"><a href="#" id="hidden" style="display:none">h</a><a href="#" id="a">a</a><button id="b">b</button></div>');
            expect(focus.focusInto(byId('e'))).toBe(true);
            expect(document.activeElement.id).toBe('a');
        });

        it('focuses the element itself when it has no control', () => {
            html('<div id="e">text</div>');
            expect(focus.focusInto(byId('e'))).toBe(true);
            expect(document.activeElement.id).toBe('e');
        });

        it('skips programmatic-only targets', () => {
            html('<div id="e"><div id="p" tabindex="-1">p</div><a href="#" id="a">a</a></div>');
            expect(focus.focusInto(byId('e'))).toBe(true);
            expect(document.activeElement.id).toBe('a');
        });
    });

    describe('focusContent()', () => {
        it('prefers the layout content container over main', () => {
            html('<main id="main-content"><div class="layout-content-container" id="c">c</div></main>');
            expect(focus.focusContent()).toBe(true);
            expect(document.activeElement.id).toBe('c');
        });

        it('falls back to main', () => {
            html('<main id="main-content">c</main>');
            expect(focus.focusContent()).toBe(true);
            expect(document.activeElement.id).toBe('main-content');
        });

        it('returns false without a content container', () => {
            html('<div>nothing</div>');
            expect(focus.focusContent()).toBe(false);
        });
    });

    describe('restore()', () => {
        it('returns the focus to the trigger once the dialog closed', () => {
            html('<button id="open">o</button><div id="m" tabindex="-1">dialog</div>');
            byId('m').focus();
            expect(focus.restore(byId('open'), byId('m'))).toBe(true);
            expect(document.activeElement.id).toBe('open');
        });

        it('returns the focus to the trigger when it was already lost to body', () => {
            html('<button id="open">o</button><div id="m">dialog</div>');
            expect(focus.restore(byId('open'), byId('m'))).toBe(true);
            expect(document.activeElement.id).toBe('open');
        });

        it('hands a trigger inside a closed dropdown to the dropdown toggle', () => {
            html('<div class="dropdown"><a href="#" id="t" data-bs-toggle="dropdown">t</a>'
                + '<ul class="dropdown-menu" style="display:none"><li><a href="#" id="item">i</a></li></ul></div>'
                + '<div id="m" tabindex="-1">dialog</div>');
            byId('m').focus();
            expect(focus.restore(byId('item'), byId('m'))).toBe(true);
            expect(document.activeElement.id).toBe('t');
        });

        it('falls back to the content container when the trigger is gone', () => {
            html('<main id="main-content"><div class="layout-content-container" id="c">c</div></main><div id="m" tabindex="-1">dialog</div>');
            byId('m').focus();
            expect(focus.restore(document.createElement('button'), byId('m'))).toBe(true);
            expect(document.activeElement.id).toBe('c');
        });

        it('leaves the focus alone when something else holds it', () => {
            html('<button id="open">o</button><button id="other">x</button><div id="m">dialog</div>');
            byId('other').focus();
            expect(focus.restore(byId('open'), byId('m'))).toBe(false);
            expect(document.activeElement.id).toBe('other');
        });

        it('works without a within element', () => {
            html('<button id="open">o</button>');
            expect(focus.restore(byId('open'), null)).toBe(true);
            expect(document.activeElement.id).toBe('open');
        });

        it('falls back to the content when the closed dropdown has no toggle', () => {
            html('<main id="main-content">c</main><div><ul class="dropdown-menu" style="display:none"><li><a href="#" id="item">i</a></li></ul></div>');
            expect(focus.restore(byId('item'), null)).toBe(true);
            expect(document.activeElement.id).toBe('main-content');
        });
    });

    describe('handOver()', () => {
        const list = () => html('<div id="list">'
            + '<div class="entry" id="e1"><a href="#" id="a1">1</a></div>'
            + '<div class="entry" id="e2"><a href="#" id="a2">2</a></div>'
            + '<div class="entry" id="e3"><a href="#" id="a3">3</a></div>'
            + '</div>');

        it('moves to the next entry', () => {
            list();
            byId('a2').focus();
            expect(focus.handOver(byId('e2'), '.entry', byId('list'))).toBe(true);
            expect(document.activeElement.id).toBe('a3');
        });

        it('moves to the previous entry when there is no next one', () => {
            list();
            byId('a3').focus();
            expect(focus.handOver(jQuery('#e3'), '.entry', byId('list'))).toBe(true);
            expect(document.activeElement.id).toBe('a2');
        });

        it('moves to the fallback when the entry is alone', () => {
            html('<div id="list"><div class="entry" id="e1"><a href="#" id="a1">1</a></div></div>');
            byId('a1').focus();
            expect(focus.handOver(byId('e1'), '.entry', byId('list'))).toBe(true);
            expect(document.activeElement.id).toBe('list');
        });

        it('acts when the focus was already lost to body', () => {
            list();
            expect(focus.handOver(byId('e2'), '.entry', byId('list'))).toBe(true);
            expect(document.activeElement.id).toBe('a3');
        });

        it('acts while the focus is in a dialog that is closing', () => {
            list();
            document.body.insertAdjacentHTML('beforeend', '<div class="modal"><button id="confirm">Delete</button></div>');
            byId('confirm').focus();
            expect(focus.handOver(byId('e2'), '.entry', byId('list'))).toBe(true);
            expect(document.activeElement.id).toBe('a3');
        });

        it('does nothing while the focus is in an open dialog', () => {
            list();
            document.body.insertAdjacentHTML('beforeend', '<div class="modal show"><input id="field"></div>');
            byId('field').focus();
            expect(focus.handOver(byId('e2'), '.entry', byId('list'))).toBe(false);
            expect(document.activeElement.id).toBe('field');
        });

        it('skips a hidden sibling', () => {
            html('<div id="list"><div class="entry" id="e1"><a href="#" id="a1">1</a></div><div class="entry" id="e2" hidden><a href="#" id="a2">2</a></div><div class="entry" id="e3"><a href="#" id="a3">3</a></div></div>');
            byId('a1').focus();
            expect(focus.handOver(byId('e1'), '.entry', byId('list'))).toBe(true);
            expect(document.activeElement.id).toBe('a3');
        });

        it('returns false for an empty element', () => {
            expect(focus.handOver(jQuery('#nothing'), '.entry', null)).toBe(false);
        });

        it('does nothing while something outside holds the focus', () => {
            list();
            document.body.insertAdjacentHTML('beforeend', '<button id="other">x</button>');
            byId('other').focus();
            expect(focus.handOver(byId('e2'), '.entry', byId('list'))).toBe(false);
            expect(document.activeElement.id).toBe('other');
        });
    });
});
