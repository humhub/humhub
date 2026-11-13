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

        it('makes the neighbour the return target of an open dialog whose trigger is in the entry', () => {
            list();
            document.body.insertAdjacentHTML('beforeend', '<div id="dlg" class="modal show"><button id="confirm">Delete</button></div>');
            jQuery('#dlg').data('hh-trigger', byId('a2'));
            byId('confirm').focus();
            expect(focus.handOver(byId('e2'), '.entry', byId('list'))).toBe(false);
            expect(document.activeElement.id).toBe('confirm');
            expect(jQuery('#dlg').data('hh-trigger')).toBe(byId('a3'));
        });

        it('leaves the return target of an open dialog alone when it is elsewhere', () => {
            list();
            document.body.insertAdjacentHTML('beforeend', '<div id="dlg" class="modal show"><button id="confirm">Delete</button></div>');
            jQuery('#dlg').data('hh-trigger', byId('a1'));
            byId('confirm').focus();
            expect(focus.handOver(byId('e2'), '.entry', byId('list'))).toBe(false);
            expect(jQuery('#dlg').data('hh-trigger')).toBe(byId('a1'));
        });

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

        it('only updates the return target while the focus is in a dialog that is closing', () => {
            list();
            document.body.insertAdjacentHTML('beforeend', '<div id="dlg" class="modal"><button id="confirm">Delete</button></div>');
            jQuery('#dlg').data('hh-trigger', byId('a2'));
            byId('confirm').focus();
            expect(focus.handOver(byId('e2'), '.entry', byId('list'))).toBe(false);
            expect(document.activeElement.id).toBe('confirm');
            expect(jQuery('#dlg').data('hh-trigger')).toBe(byId('a3'));
        });

        it('makes the neighbour the return target when the dialog trigger is gone', () => {
            list();
            document.body.insertAdjacentHTML('beforeend', '<div id="dlg" class="modal show"><button id="confirm">Delete</button></div>');
            const gone = document.createElement('button');
            jQuery('#dlg').data('hh-trigger', gone);
            byId('confirm').focus();
            expect(focus.handOver(byId('e2'), '.entry', byId('list'))).toBe(false);
            expect(jQuery('#dlg').data('hh-trigger')).toBe(byId('a3'));
        });

        it('leaves a hidden trigger of another entry alone', () => {
            list();
            byId('a1').hidden = true;
            document.body.insertAdjacentHTML('beforeend', '<div id="dlg" class="modal show"><button id="confirm">Delete</button></div>');
            jQuery('#dlg').data('hh-trigger', byId('a1'));
            byId('confirm').focus();
            expect(focus.handOver(byId('e2'), '.entry', byId('list'))).toBe(false);
            expect(jQuery('#dlg').data('hh-trigger')).toBe(byId('a1'));
        });

        it('falls back to the entry itself when the neighbour has no control', () => {
            html('<div id="list"><div class="entry" id="e1"><a href="#" id="a1">1</a></div><div class="entry" id="e2">plain</div></div>');
            byId('a1').focus();
            expect(focus.handOver(byId('e1'), '.entry', byId('list'))).toBe(true);
            expect(document.activeElement.id).toBe('e2');
        });

        it('falls back to the content when there is neither neighbour nor fallback', () => {
            html('<main id="main-content"><div id="list"><div class="entry" id="e1"><a href="#" id="a1">1</a></div></div></main>');
            byId('a1').focus();
            expect(focus.handOver(byId('e1'), '.entry', null)).toBe(true);
            expect(document.activeElement.id).toBe('main-content');
        });

        it('returns false for a detached element', () => {
            const detached = document.createElement('div');
            expect(focus.handOver(detached, '.entry', null)).toBe(false);
        });

        it('does nothing while the focus is in an open dialog', () => {
            list();
            document.body.insertAdjacentHTML('beforeend', '<div class="modal show"><input id="field"></div>');
            byId('field').focus();
            expect(focus.handOver(byId('e2'), '.entry', byId('list'))).toBe(false);
            expect(document.activeElement.id).toBe('field');
        });

        it('skips programmatic-only targets when picking the neighbour control', () => {
            html('<div id="list"><div class="entry" id="e1"><a href="#" id="a1">1</a></div>'
                + '<div class="entry" id="e2"><div id="p" tabindex="-1">p</div><a href="#" id="a2">2</a></div></div>');
            byId('a1').focus();
            expect(focus.handOver(byId('e1'), '.entry', byId('list'))).toBe(true);
            expect(document.activeElement.id).toBe('a2');
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

    describe('controls()', () => {
        it('lists the shown controls that Tab reaches, in document order', () => {
            html('<div id="d"><a id="a" href="#">a</a><button id="off" tabindex="-1">x</button>'
                + '<select id="s" tabindex="-1"></select><div hidden><input id="h"></div>'
                + '<input id="i"><button id="dis" disabled>x</button><div id="ce" contenteditable="true"></div></div>');
            expect(focus.controls(byId('d')).map((el) => el.id)).toEqual(['a', 'i', 'ce']);
        });
    });

    describe('trap()', () => {
        const tab = (shiftKey = false) => new KeyboardEvent('keydown', {key: 'Tab', shiftKey, cancelable: true});
        const dialog = '<button id="before">x</button><div id="dlg" class="modal show" tabindex="-1">'
            + '<button id="first">1</button><input id="middle"><button id="last">3</button></div>';

        it('goes from the last control to the first and back', () => {
            html(dialog);
            byId('last').focus();
            const forward = tab();
            expect(focus.trap(forward, byId('dlg'))).toBe(true);
            expect(forward.defaultPrevented).toBe(true);
            expect(document.activeElement.id).toBe('first');

            expect(focus.trap(tab(true), byId('dlg'))).toBe(true);
            expect(document.activeElement.id).toBe('last');
        });

        it('lets Tab move between the controls inside', () => {
            html(dialog);
            byId('first').focus();
            const evt = tab();
            expect(focus.trap(evt, byId('dlg'))).toBe(false);
            expect(evt.defaultPrevented).toBe(false);
        });

        it('takes Shift+Tab from the dialog itself to the last control', () => {
            html(dialog);
            byId('dlg').focus();
            expect(focus.trap(tab(true), byId('dlg'))).toBe(true);
            expect(document.activeElement.id).toBe('last');
        });

        it('brings the focus back from the page behind', () => {
            html(dialog);
            byId('before').focus();
            expect(focus.trap(tab(), byId('dlg'))).toBe(true);
            expect(document.activeElement.id).toBe('first');

            loseFocus();
            expect(focus.trap(tab(true), byId('dlg'))).toBe(true);
            expect(document.activeElement.id).toBe('last');
        });

        it('leaves the focus to another dialog opened on top', () => {
            html(dialog + '<div role="dialog" aria-label="Emoji picker"><input id="search"></div>');
            byId('search').focus();
            const evt = tab();
            expect(focus.trap(evt, byId('dlg'))).toBe(false);
            expect(evt.defaultPrevented).toBe(false);
            expect(document.activeElement.id).toBe('search');
        });

        it('ignores other keys and a Tab handled already', () => {
            html(dialog);
            byId('last').focus();
            expect(focus.trap(new KeyboardEvent('keydown', {key: 'Enter'}), byId('dlg'))).toBe(false);
            const handled = tab();
            handled.preventDefault();
            expect(focus.trap(handled, byId('dlg'))).toBe(false);
            expect(document.activeElement.id).toBe('last');
        });
    });
});
