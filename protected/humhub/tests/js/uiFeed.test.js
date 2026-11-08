import { beforeEach, describe, expect, it, vi } from 'vitest';

// jsdom has no layout: an element counts as shown unless it or an ancestor is `hidden` or has an
// inline `display: none` (ui.focus prefers checkVisibility() over getClientRects()), and nothing scrolls.
Element.prototype.checkVisibility = function () {
    for (let el = this; el; el = el.parentElement) {
        if (el.hidden || el.style.display === 'none') {
            return false;
        }
    }
    return true;
};
Element.prototype.scrollIntoView = function () {};

await import('../../resources/js/humhub/humhub.ui.focus.js');
await import('../../resources/js/humhub/humhub.ui.feed.js');
const feed = globalThis.humhub.modules['ui.feed'];

const html = (markup) => {
    document.body.innerHTML = markup;
};
const byId = (id) => document.getElementById(id);
const active = () => document.activeElement && document.activeElement.id;
const press = (el, key, init = {}) => {
    const evt = new KeyboardEvent('keydown', { key, bubbles: true, cancelable: true, ...init });
    el.dispatchEvent(evt);
    return evt;
};
const article = (id, inner = '') => `<div id="${id}" role="article" tabindex="0"><a id="${id}-link" href="#">${id}</a>${inner}</div>`;

const STREAM = `
    <button id="before">before</button>
    <div id="feed" role="feed">
        ${article('a')}
        ${article('b')}
        <div id="hidden-entry" role="article" tabindex="0" style="display: none">hidden</div>
        ${article('c')}
    </div>
    <button id="after">after</button>
    <aside><a id="sidebar-link" href="#">sidebar</a></aside>`;

describe('ui.feed', () => {
    beforeEach(() => {
        if (document.activeElement && document.activeElement !== document.body) {
            document.activeElement.blur();
        }
        document.body.innerHTML = '';
    });

    describe('PageDown and PageUp', () => {
        it('move between the shown entries', () => {
            html(STREAM);
            byId('a').focus();

            expect(press(byId('a'), 'PageDown').defaultPrevented).toBe(true);
            expect(active()).toBe('b');
            press(byId('b'), 'PageDown');
            expect(active()).toBe('c'); // the hidden entry is skipped
            press(byId('c'), 'PageUp');
            expect(active()).toBe('b');
        });

        it('count from the entry around a control inside it', () => {
            html(STREAM);
            byId('a-link').focus();
            press(byId('a-link'), 'PageDown');
            expect(active()).toBe('b');
        });

        it('go to the entry after a control that is in no entry', () => {
            html(`<div id="feed" role="feed"><a id="more" href="#">Show more</a>${article('a')}${article('b')}</div>`);
            byId('more').focus();
            press(byId('more'), 'PageDown');
            expect(active()).toBe('a');

            html(`<div id="feed" role="feed">${article('a')}${article('b')}<a id="more" href="#">Show more</a></div>`);
            byId('more').focus();
            press(byId('more'), 'PageUp');
            expect(active()).toBe('b');
        });

        it('leave the keys to a text field and with Shift or Alt', () => {
            html(`<div id="feed" role="feed">${article('a', '<input id="field">')}${article('b')}</div>`);
            byId('field').focus();
            expect(press(byId('field'), 'PageDown').defaultPrevented).toBe(false);
            expect(active()).toBe('field');

            byId('a').focus();
            expect(press(byId('a'), 'PageDown', { shiftKey: true }).defaultPrevented).toBe(false);
            expect(press(byId('a'), 'PageDown', { altKey: true }).defaultPrevented).toBe(false);
            expect(active()).toBe('a');
        });

        it('do nothing outside a feed', () => {
            html(`${article('a')}${article('b')}`);
            byId('a').focus();
            expect(press(byId('a'), 'PageDown').defaultPrevented).toBe(false);
            expect(active()).toBe('a');
        });
    });

    describe('at the end', () => {
        it('asks the feed for more and follows once it is no longer busy', async () => {
            html(STREAM);
            const onMore = vi.fn((evt) => {
                byId('feed').setAttribute('aria-busy', 'true');
                expect(evt.detail.direction).toBe('next');
            });
            byId('feed').addEventListener('humhub:feed:more', onMore);
            byId('c').focus();

            expect(press(byId('c'), 'PageDown').defaultPrevented).toBe(false); // the page may scroll
            expect(onMore).toHaveBeenCalledTimes(1);
            expect(active()).toBe('c');

            byId('feed').insertAdjacentHTML('beforeend', article('d'));
            byId('feed').removeAttribute('aria-busy');
            await Promise.resolve(); // MutationObserver callbacks are microtasks
            expect(active()).toBe('d');
        });

        it('does not follow when the focus went elsewhere meanwhile', async () => {
            html(STREAM);
            byId('feed').addEventListener('humhub:feed:more', () => byId('feed').setAttribute('aria-busy', 'true'));
            byId('c').focus();
            press(byId('c'), 'PageDown');

            byId('after').focus();
            byId('feed').insertAdjacentHTML('beforeend', article('d'));
            byId('feed').removeAttribute('aria-busy');
            await Promise.resolve();
            expect(active()).toBe('after');
        });

        it('stays when nothing loads', () => {
            html(STREAM);
            byId('c').focus();
            press(byId('c'), 'PageDown');
            expect(active()).toBe('c');
            byId('a').focus();
            press(byId('a'), 'PageUp');
            expect(active()).toBe('a');
        });
    });

    describe('Ctrl+End and Ctrl+Home', () => {
        it('leave the feed to the next or previous control', () => {
            html(STREAM);
            byId('b').focus();
            expect(press(byId('b'), 'End', { ctrlKey: true }).defaultPrevented).toBe(true);
            expect(active()).toBe('after');

            byId('b-link').focus();
            press(byId('b-link'), 'Home', { metaKey: true });
            expect(active()).toBe('before');
        });

        it('skip a hidden control after the feed', () => {
            html(STREAM);
            byId('after').style.display = 'none';
            byId('a').focus();
            press(byId('a'), 'End', { ctrlKey: true });
            expect(active()).toBe('sidebar-link');
        });

        it('do nothing when there is no control to go to', () => {
            html(`<div id="feed" role="feed">${article('a')}</div>`);
            byId('a').focus();
            expect(press(byId('a'), 'End', { ctrlKey: true }).defaultPrevented).toBe(false);
            expect(active()).toBe('a');
        });
    });

    describe('a feed inside an entry', () => {
        const NESTED = `
            <div id="feed" role="feed">
                ${article('p1', `
                    <div id="comments" role="feed">${article('c1')}${article('c2')}</div>
                    <button id="comment-form">Comment</button>`)}
                ${article('p2', '<div id="empty" role="feed"><a id="p2-reply" href="#">Reply</a></div>')}
            </div>`;

        it('takes the keys while the focus is in it', () => {
            html(NESTED);
            byId('c1').focus();
            press(byId('c1'), 'PageDown');
            expect(active()).toBe('c2');
            press(byId('c2'), 'PageDown'); // the last comment: the post feed must not move to p2
            expect(active()).toBe('c2');
            press(byId('c2'), 'End', { ctrlKey: true });
            expect(active()).toBe('comment-form');
        });

        it('is skipped by the outer feed, whose entries are the posts only', () => {
            html(NESTED);
            expect(feed.articles(byId('feed')).map((el) => el.id)).toEqual(['p1', 'p2']);
            byId('comment-form').focus();
            press(byId('comment-form'), 'PageDown');
            expect(active()).toBe('p2');
        });

        it('leaves the keys to the feed around it while it is empty', () => {
            html(NESTED);
            byId('p2-reply').focus();
            press(byId('p2-reply'), 'PageUp');
            expect(active()).toBe('p1');
        });
    });
});
